"""Exercise operational counting boundaries and failures without any API credentials."""
import importlib.util
from pathlib import Path
import os
import unittest
from unittest.mock import patch

ROOT = Path(__file__).resolve().parents[1]
path = ROOT/'resources/content/articles/grafana-installation-zabbix-integration/noc-collector.py'
spec = importlib.util.spec_from_file_location('noc_collector',path)
collector = importlib.util.module_from_spec(spec)
spec.loader.exec_module(collector)

class CollectorTests(unittest.TestCase):
    def test_active_passive_and_unknown_availability_partition(self):
        examples = [({'active_available':'1','interfaces':[]},1),
                    ({'active_available':'0','interfaces':[{'available':'2'}]},2),
                    ({'active_available':'2','interfaces':[{'available':'1'}]},1),
                    ({'active_available':'0','interfaces':[{'available':'0'}]},0)]
        for host,expected in examples:self.assertEqual(expected,collector.availability(host))

    def test_empty_scope_and_api_failure_never_send_zero_metrics(self):
        with patch.dict(os.environ,{'ZABBIX_GROUP_IDS':'10'}), patch.object(collector.subprocess,'run') as sender:
            with patch.object(collector,'api',return_value=[]):
                with self.assertRaisesRegex(RuntimeError,'Empty permitted'):collector.collect()
            with patch.object(collector,'api',side_effect=RuntimeError('API unavailable')):
                with self.assertRaisesRegex(RuntimeError,'API unavailable'):collector.collect()
            sender.assert_not_called()

    def test_problem_scope_and_sender_rejection(self):
        calls=[]
        def api(method,params):
            calls.append((method,params))
            return [{'hostid':'100','active_available':'1','interfaces':[]}] if method=='host.get' else '0'
        env={'ZABBIX_GROUP_IDS':'10','ZABBIX_SUMMARY_HOST':'noc-production','ZABBIX_SERVER':'192.0.2.10',
             'ZABBIX_PSK_IDENTITY':'noc-production','ZABBIX_PSK_FILE':'/protected/test.psk'}
        with patch.dict(os.environ,env), patch.object(collector,'api',side_effect=api), patch.object(collector.subprocess,'run') as sender:
            sender.return_value.returncode=2
            with self.assertRaisesRegex(RuntimeError,'Sender rejected'):collector.collect()
            for method,params in calls[1:]:
                self.assertEqual(['100'],params['hostids'])
                self.assertFalse(params['recent'])
            self.assertEqual([5],calls[2][1]['severities'])
            self.assertEqual([4],calls[3][1]['severities'])
            payload=sender.call_args.kwargs['input']
            self.assertIn('noc-production noc.hosts.unknown 0',payload)
            self.assertIn('noc-production noc.last.success ',payload)
            self.assertNotIn('Bearer',payload)

if __name__=='__main__':unittest.main()
