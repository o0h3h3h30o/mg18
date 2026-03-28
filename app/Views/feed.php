<?php header ("Content-Type:text/xml"); ?>
<?php echo '<?xml version="1.0" encoding="UTF-8"?>';?>
<feed xmlns="http://www.w3.org/2005/Atom">
    <title type="text">Manga18.club</title>
    <subtitle type="html"><![CDATA[All content on https://manga18.club and https://manga18.us is collected on the internet. So there are any issues regarding selling rights, please contact me directly at the email address contact@manga18.club If your request is reasonable we will remove it immediately.
Sincerely thank you !!]]></subtitle>
    <link href="https://manga18.club/feed"></link>
    <id>https://manga18.club/feed</id>
    <link rel="alternate" type="text/html" href="https://manga18.club/feed" ></link>
    <link rel="self" type="application/atom+xml" href="https://manga18.club/feed" ></link>
        <updated><?=$datePublished?></updated>
        <?php foreach ($listChapters as $key => $value) { ?>        	        
        <entry>
            <author>
                <name></name>
            </author>
            <title type="text"><![CDATA[<?=$value->manga_name?> #<?=$value->chapter_1?>]]></title>
            <link rel="alternate" type="text/html" href="https://manga18.club/manhwa/<?=$value->manga_slug?>/<?=$value->chap_1_slug?>"></link>
            <id><?=base_url();?>manhwa/<?=$value->manga_slug?>/<?=$value->chap_1_slug?></id>
            <summary type="html"><![CDATA[<?=$value->manga_name.' - Chapter '.$value->chapter_1?>]]></summary>
            <content type="html"><![CDATA[]]></content>
            
            <updated><?=$value->publishtime?></updated>
        </entry>
    	<?php } ?>
</feed>