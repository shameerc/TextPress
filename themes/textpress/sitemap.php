<?php 
header("Content-Type:text/xml");
echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>
<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
	<url>
		<loc><?php echo $baseUrl;?></loc>
		<lastmod><?php echo date('c', strtotime('-1 days'));?></lastmod>
		<changefreq>weekly</changefreq>
		<priority>1</priority>
	</url>
	<?php 
		if (!empty($sitemapData)) {
			foreach ($sitemapData as $data) {
				?>
				<url>
					<loc><?php echo $data['loc']; ?></loc>
					<lastmod><?php echo $data['lastmod']; ?></lastmod>
					<changefreq><?php echo $data['changefreq']; ?></changefreq>
					<priority><?php echo $data['priority']; ?></priority>
				</url>
				<?php
			}
		}
	?>
</urlset>