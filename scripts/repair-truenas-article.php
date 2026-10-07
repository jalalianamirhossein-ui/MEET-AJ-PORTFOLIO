<?php

// Repair the maintained source without importing over unrelated CMS edits.
$root = dirname(__DIR__);
$path = $root.'/resources/legacy/articles/truenas-zfs-enterprise.html';
$html = file_get_contents($path);
$html = str_replace('class="article-page theme-other"', 'class="article-page theme-qnap"', $html);
$title = 'TrueNAS Enterprise NAS with ZFS';
$description = 'Build a TrueNAS storage server with ZFS pools, datasets, SMB, NFS, iSCSI, snapshots, replication and a tested backup plan.';
$html = preg_replace('~(<h1 class="article-title hero-title" data-en=")[^"]*(")~', '$1'.$title.'$2', $html, 1);
$html = preg_replace('~(<p class="article-excerpt hero-subtitle" data-en=")[^"]*(")~', '$1'.$description.'$2', $html, 1);
$html = preg_replace_callback('~<p>\|.*?\|</p>~s', function ($match) {
    $lines = preg_split('/\R/u', trim(strip_tags($match[0])));
    $cells = fn ($line) => array_map('trim', explode('|', trim(trim($line), '|')));
    $output = '<div class="article-table-wrap"><table class="article-table"><thead><tr>';
    foreach ($cells($lines[0]) as $value) { $output .= '<th scope="col">'.htmlspecialchars($value, ENT_QUOTES, 'UTF-8').'</th>'; }
    $output .= '</tr></thead><tbody>';
    foreach (array_slice($lines, 2) as $line) {
        $output .= '<tr>';
        foreach ($cells($line) as $value) { $output .= '<td>'.htmlspecialchars($value, ENT_QUOTES, 'UTF-8').'</td>'; }
        $output .= '</tr>';
    }
    return $output.'</tbody></table></div>';
}, $html);
$before = 'NAS فایل یا Block Storage را از طریق شبکه در اختیار Clientها و Hypervisorها قرار می‌دهد.';
$after = 'NAS فایل‌ها را از طریق پروتکل‌هایی مانند SMB و NFS در اختیار کاربران و میزبان‌ها قرار می‌دهد. TrueNAS علاوه بر قابلیت NAS، با iSCSI ذخیره‌سازی بلوکی نیز ارائه می‌کند؛ این کاربرد از نظر معماری در دستهٔ SAN قرار می‌گیرد.';
$html = str_replace($before, $after, $html);
$html = str_replace('Production-oriented و مبتنی بر مستندات رسمی فعلی TrueNAS.', 'این راهنما برای TrueNAS مبتنی بر Linux نوشته شده است؛ مسیرهای نمونه با مستندات نسخهٔ 25.04 تطبیق داده شده‌اند.', $html);
$html = str_replace('Boot Device حداقل 20 GB', 'SSD برای Boot با ظرفیت حداقل 20 GB مطابق راهنمای نصب 25.04؛ دیسک Boot از دیسک‌های Pool جدا باشد', $html);
$html = str_replace('Bare Metal، Appliance یا VM با طراحی دقیق', 'Bare Metal یا Appliance؛ VM برای آزمایش و با بررسی محدودیت‌های پشتیبانی', $html);
$html = str_replace('https://www.truenas.com/docs/scale/gettingstarted/tnhardwareguide/', 'https://www.truenas.com/docs/scale/25.04/gettingstarted/scalehardwareguide/', $html);
$html = str_replace('https://www.truenas.com/docs/scale/gettingstarted/', 'https://www.truenas.com/docs/scale/25.04/gettingstarted/', $html);
$html = str_replace('<li>[ ] ', '<li>', $html);
$faq = [
    ['آیا RAIDZ و Snapshot جای نسخهٔ پشتیبان را می‌گیرند؟', 'خیر. RAIDZ برای تحمل خرابی دیسک و Snapshot برای بازگشت به یک زمان مشخص است. برای خرابی کل Pool، حذف مخرب و حادثهٔ سایت، نسخهٔ مستقل و خارج از سایت لازم است.'],
    ['تفاوت Dataset و ZVOL چیست؟', 'Dataset یک فایل‌سیستم با سهمیه، ACL و Snapshot مستقل است و برای اشتراک SMB یا NFS استفاده می‌شود. ZVOL یک دستگاه بلوکی است و معمولاً پشت Extent در iSCSI قرار می‌گیرد.'],
    ['برای ماشین‌های مجازی Mirror مناسب‌تر است یا RAIDZ2؟', 'انتخاب به IOPS، تأخیر، ظرفیت و الگوی بار بستگی دارد. Mirror VDEVها معمولاً برای بار تصادفی ماشین‌های مجازی مناسب‌اند؛ RAIDZ2 ظرفیت و تحمل خرابی دو دیسک در هر VDEV را ارائه می‌کند. انتخاب نهایی را با بار واقعی آزمایش کنید.'],
    ['چرا دیسک Boot باید از دیسک‌های Pool جدا باشد؟', 'نصب TrueNAS دیسک انتخاب‌شده را پاک می‌کند. جداسازی Boot از داده‌ها خطر انتخاب اشتباه و تداخل نقش دیسک‌ها را کم می‌کند. قبل از نصب، مدل و شمارهٔ سریال دیسک مقصد را بررسی کنید.'],
    ['چگونه دسترسی به SMB را محدود کنیم؟', 'Dataset مستقل بسازید، ACL را به گروه‌های موردنیاز بدهید و دسترسی شبکه به سرویس را محدود کنید. با یک کاربر مجاز و یک کاربر غیرمجاز، خواندن، نوشتن و حذف را آزمایش کنید.'],
    ['آیا فعال کردن Deduplication همیشه فضای بیشتری آزاد می‌کند؟', 'خیر. نتیجه به تکرار واقعی داده‌ها و منابع سیستم بستگی دارد و ممکن است هزینهٔ حافظه و کارایی از صرفه‌جویی بیشتر باشد. بدون اندازه‌گیری و آزمایش، آن را فعال نکنید.'],
    ['موفق بودن Replication برای اطمینان از بازیابی کافی است؟', 'خیر. علاوه بر بررسی Job و Snapshotهای مقصد، بازیابی فایل یا Dataset را عملاً آزمایش کنید. Retention مقصد و کلیدهای رمزنگاری را جدا از سرور اصلی نگهداری کنید.'],
    ['هنگام Degraded شدن Pool چه کاری انجام دهیم؟', 'از Dashboard و zpool status وضعیت را بررسی کنید، دیسک معیوب را با شمارهٔ سریال شناسایی و سلامت Backup را تأیید کنید. تعویض را از روش پشتیبانی‌شدهٔ رابط TrueNAS انجام دهید و پیشرفت Resilver را پایش کنید.'],
];
if (! str_contains($html, 'id="faq"')) {
    $faqHtml = '<section id="faq" class="article-section"><h2>پرسش‌های متداول TrueNAS</h2><div class="article-faq">';
    foreach ($faq as [$question, $answer]) {
        $faqHtml .= '<div class="article-faq-item"><h3 class="article-faq-question">'.$question.'</h3><div class="article-faq-answer"><p>'.$answer.'</p></div></div>';
    }
    $html = str_replace('<section id="section-30">', $faqHtml.'</div></section><section id="section-30">', $html);
}
file_put_contents($path, $html);
$markdownPath = $root.'/resources/content/articles/truenas-zfs-enterprise-nas/article.fa.md';
$markdown = file_get_contents($markdownPath);
$markdown = str_replace($before, $after, $markdown);
$markdown = str_replace('Production-oriented و مبتنی بر مستندات رسمی فعلی TrueNAS.', 'این راهنما برای TrueNAS مبتنی بر Linux نوشته شده است؛ مسیرهای نمونه با مستندات نسخهٔ 25.04 تطبیق داده شده‌اند.', $markdown);
$markdown = str_replace('Boot Device حداقل 20 GB', 'SSD برای Boot با ظرفیت حداقل 20 GB مطابق راهنمای نصب 25.04؛ دیسک Boot از دیسک‌های Pool جدا باشد', $markdown);
$markdown = str_replace('Bare Metal، Appliance یا VM با طراحی دقیق', 'Bare Metal یا Appliance؛ VM برای آزمایش و با بررسی محدودیت‌های پشتیبانی', $markdown);
$markdown = str_replace('https://www.truenas.com/docs/scale/gettingstarted/tnhardwareguide/', 'https://www.truenas.com/docs/scale/25.04/gettingstarted/scalehardwareguide/', $markdown);
$markdown = str_replace('https://www.truenas.com/docs/scale/gettingstarted/', 'https://www.truenas.com/docs/scale/25.04/gettingstarted/', $markdown);
if (! str_contains($markdown, '## پرسش‌های متداول TrueNAS')) {
    $faqMarkdown = "## پرسش‌های متداول TrueNAS\n\n";
    foreach ($faq as [$question, $answer]) { $faqMarkdown .= '### '.$question."\n\n".$answer."\n\n"; }
    $markdown = str_replace('## منابع رسمی', $faqMarkdown.'## منابع رسمی', $markdown);
}
file_put_contents($markdownPath, $markdown);
echo "TrueNAS maintained HTML and Markdown repaired.\n";
