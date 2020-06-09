{include file='header.tpl'}

<center>

<img src='{$podcast->image}' class="podcastimage" />

<h2>{$podcast->name}</h2>
</center>

<br />

<center>
<form action='/profile.php' method='post'>
{if !isset($user) || !$podcast->subscribed($user)}
	<button type='submit' name='subscribe' class='btn-round-primary btn-primary btn' value='{$podcast->feed}'>Subscribe</button>
{else}
	<button type='submit' name='unsubscribe' class='btn-round-primary btn-primary btn' value='{$podcast->feed}'>Unsubscribe</button>
{/if}
</form>
</center>


<br />

{$podcast->description}

<br /><br /><br />

<ul>
{foreach from=$episodes item=episode}
	<li style='height: 72px; list-style-type: none;'><img src='{$podcast->image}' style='width: 64px; height: 64px; border: 1px solid black; margin-right: 1em; float: left;'/><div style='width: 50%; float: left; height: 18px; overflow: hidden;'>{$episode['name']}</div>
		<div style='width: 50%; float: right;'>
			<audio controls preload="none" style='width: 100%;'>
				<source src="{$episode['audiourl']}" />
			</audio>
		</div>
	</li>
{/foreach}
</ul>

<br />

{include file='footer.tpl'}
