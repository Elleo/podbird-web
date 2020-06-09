{include file='header.tpl'}
<script type='text/javascript'>
	$(document).ready(function() {
		var img = new Image();
		img.src = '/images/settings.png';
		img.src = '/images/podcasts.png';
		img.src = '/images/search.png';
		img.src = '/images/player.png';
		img.src = '/images/themes.png';
	});
</script>

<br />
<center>
Podbird is a friendly podcast manager for Ubuntu phones, tablets and desktops.
<br /><br />

<div class="row">
<div class="col-md-4">

	<div class='feature' onmouseover='$("#featureimg").attr("src", "/images/episodes.png");'>
		<h4>Episodes at a glance</h4>
		<p>See what's new from all your favourite podcasts as soon as you start Podbird.</p>
	</div>

	<div class='feature' onmouseover='$("#featureimg").attr("src", "/images/settings.png");'>
		<h4>Comprehensive settings</h4>
		<p>Podbird has a wide range of settings allowing you to configure automatic downloads, deletion of listened podcasts, customise seeking and choose from different display modes.</p>
	</div>

	<div class='feature' onmouseover='$("#featureimg").attr("src", "/images/podcasts.png");'>
		<h4>Podcast overview</h4>
		<p>Find out quickly which podcasts have episodes that you haven't listened to yet.</p>
	</div>

</div>

<div class="col-md-4">

	<img id='featureimg' src="/images/episodes.png" style='width: 100%; margin-top: 2.5em; margin-bottom: 2.5em;' />

</div>

<div class="col-md-4">

	<div class='rfeature' onmouseover='$("#featureimg").attr("src", "/images/search.png");'>
		<h4>Podcast search</h4>
		<p>Easily search through millions of podcasts in the iTunes<sup>&reg;</sup> database.</p>
	</div>

	<div class='rfeature' onmouseover='$("#featureimg").attr("src", "/images/player.png");'>
		<h4>Advanced player</h4>
		<p>Podbird's integrated player allows you to queue podcasts and remembers your position from last time.</p>
	</div>

	<div class='rfeature' onmouseover='$("#featureimg").attr("src", "/images/themes.png");'>
		<h4>Light and dark themes</h4>
		<p>Themes allow you to customise the appearance of Podbird to your liking.</p>
	</div>
	
</div>

</div>

<br />

{include file='footer.tpl'}
