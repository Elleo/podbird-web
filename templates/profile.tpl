{include file='header.tpl'}

<center>
<h2>Subscriptions</h2>
</center>

<br />

<form action='/profile.php' method='post'>
<ul>
{foreach from=$subscriptions item=podcast}
	<a href='/podcast.php?id={$podcast->id}'><li style='height: 72px;'><img src='{$podcast->image}' style='width: 64px; height: 64px; border: 1px solid black; margin-right: 1em;'/> {$podcast->name} <button class='btn-round-primary btn-primary btn' type='submit' name='unsubscribe' value='{$podcast->feed}' style='float: right; margin-left: 1em; margin-right: 1em; margin-top: 1em;'>Unsubscribe</button></li></a>
{/foreach}
</ul>

<br />

<center>
<h2>Recommendations</h2>
</center>

<br />
<ul>
{foreach from=$recommendations item=podcast}
        <a href='/podcast.php?id={$podcast->id}'><li style='height: 72px;'><img src='{$podcast->image}' style='width: 64px; height: 64px; border: 1px solid black; margin-right: 1em;'/> {$podcast->name} <button class='btn-round-primary btn-primary btn' type='submit' name='subscribe' value='{$podcast->feed}' style='float: right; margin-left: 1em; margin-right: 1em; margin-top: 1em;'>Subscribe</button></li></a>
{/foreach}
</form>
</ul>
<br />

{include file='footer.tpl'}
