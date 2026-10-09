"""Source compilation and deliberate content-loss regressions."""
import sys, unittest
from pathlib import Path
sys.path.insert(0, str(Path(__file__).resolve().parents[1] / 'scripts'))
from article_technical_content import ROOT, inputs, enrich, technical_issues, render

class TechnicalContentTest(unittest.TestCase):
    def test_every_reviewed_source_compiles_stably_without_metadata_drift(self):
        for slug in inputs()['articles']:
            source=(ROOT/'resources/legacy/articles'/f'{slug}.html').read_text(encoding='utf-8')
            with self.subTest(slug=slug):
                self.assertEqual(enrich(source,slug),source)
                self.assertEqual(technical_issues(source,slug),{})

    def test_missing_command_is_not_satisfied_by_its_name_in_prose(self):
        slug='windows-cmd-common-network-commands'
        source=(ROOT/'resources/legacy/articles'/f'{slug}.html').read_text(encoding='utf-8')
        command='<pre dir="ltr"><code class="language-batch">ipconfig</code></pre>'
        self.assertIn(command,source)
        broken=source.replace(command,'<p>ipconfig</p>',1)
        self.assertIn('missing_or_changed_artifact',technical_issues(broken,slug))

    def test_incomplete_translation_and_missing_target_fail_compilation(self):
        with self.assertRaises(ValueError):
            render({'type':'p','en':'Actual instructions','fa':''})
        with self.assertRaises(ValueError):
            enrich('<article><section id="intro"><h2>Overview</h2><p>Body</p></section></article>','windows-cmd-common-network-commands')

    def test_firewall_comments_are_not_executable_rules(self):
        slug='mikrotik-firewall-hardening-input-forward-chain'
        source=(ROOT/'resources/legacy/articles'/f'{slug}.html').read_text(encoding='utf-8')
        self.assertEqual(technical_issues(source+'<pre><code># action=drop limit=10,5:packet is unsafe</code></pre>',slug),{})
        self.assertIn('rate_limited_deny',technical_issues(source+'<pre><code>add chain=forward action=drop limit=10,5:packet</code></pre>',slug))

if __name__=='__main__':unittest.main()
