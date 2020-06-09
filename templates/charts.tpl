{include file='header.tpl'}

<center>
<h2>Top Podcasts</h2>
</center>

<br />

<form action='/profile.php' method='post'>
<ol>
{foreach from=$chart item=podcast}
        <a href='/podcast.php?id={$podcast[0]->id}'><li style='height: 72px; list-style-type: none;'><img src='{$podcast[0]->image}' style='width: 64px; height: 64px; border: 1px solid black; margin-right: 1em; float: left;'/><div style='max-width: 500px; height: 72px; text-overflow: ellipsis; overflow: hidden; float: left; padding-top: 10px;'> {$podcast[0]->name} ({$podcast[1]} listeners)</div>
	{if !isset($user) || !$podcast[0]->subscribed($user)}
		<button class='btn-round-primary btn-primary btn' type='submit' name='subscribe' value='{$podcast[0]->feed}' style='float: right; margin-left: 1em; margin-right: 1em; margin-top: 1em;'>Subscribe</button></li></a>
	{else}
		<button class='btn-round-primary btn-primary btn' type='submit' name='unsubscribe' value='{$podcast[0]->feed}' style='float: right; margin-left: 1em; margin-right: 1em; margin-top: 1em;'>Unsubscribe</button></li></a>
	{/if}
{/foreach}
</ol>
</form>

{include file='footer.tpl'}
