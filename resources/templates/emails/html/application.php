<style>
	body
	{
		font-family: Helvetica, Arial;
	}

	table
	{
		width: 500px;
		border-collapse: collapse;
		line-height: 18px;
	}

	tr.last th, tr.last td
	{
		border-bottom: none;
	}

	th, td
	{
		text-align: left;
		border-bottom: 1px solid #000;
		padding: 12px 8px 10px;
	}

	th
	{
		vertical-align: middle;
	}

	td
	{
		vertical-align: top;
	}
</style>

<h1>Studio City Career Application</h1>

<table>
	<tr>
		<th>First Name</th>
		<td><?= $first_name ?></td>
	</tr>
	<tr>
		<th>Last Name</th>
		<td><?= $last_name ?></td>
	</tr>
	<tr>
		<th>Email</th>
		<td><?= $email ?></td>
	</tr>
	<tr>
		<th>Cell Phone</th>
		<td><?= $cell_phone ?></td>
	</tr>
	<?php if ( $alt_phone !== null ): ?>
		<tr>
			<th>Alt Phone</th>
			<td><?= $alt_phone ?></td>
		</tr>
	<?php endif ?>
	<tr>
		<th>Website/Portfolio URL</th>
		<td><?= $website_url ?></td>
	</tr>
	<tr>
		<th>How did you hear about Studio City?</th>
		<td><?= $referer ?></td>
	</tr>
	<tr>
		<th>Time</th>
		<td><?= $time ?></td>
	</tr>
	<tr class="last">
		<th>IP Address:</th>
		<td><?= $ip_address ?></td>
	</tr>
</table>