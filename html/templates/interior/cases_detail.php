<div class = "case_detail_bar">

	<div class='case_title'>

		<h2>
		<?php

		if ($case_data->organization)
			{echo $case_data->organization;}
		else
			{echo $case_data->first_name . " " . $case_data->last_name;}

		?>
		</h2>

	</div>

	<div class="assigned_people">

		<ul>

			<li class="slide closed"><a>Assigned:</a></li>


		<?php if ($assigned_users_data){ foreach ($assigned_users_data as $user)
		{
			$thumbnail = thumbify($user['picture_url']);

			if ($user['user_case_status'] == "active")
			{
			echo "<li class = 'active'><span><img class='thumbnail-mask' tabindex='1' id='imgid_" . $user['case_id'] . "_" . $user['username']  . "' src='$thumbnail' title='" . $user['first_name'] . " " . $user['last_name'] . "'></span></li>";
			}

			else

			{
			echo "<li class = 'inactive'><span><img  class='thumbnail-mask' tabindex='1' id='imgid_" . $user['case_id'] . "_" . $user['username']  . "' src='$thumbnail' title='" . $user['first_name'] . " " . $user['last_name'] . "'></span></li>";
			}


		}}

		if ($_SESSION['permissions']['assign_cases'] == "1")
		{ echo "<li><span></span><img class='thumbnail-mask user_add_button' id='add_button_" . $id . "' src='people/tn_add_user.png'></span></li>";}
		?>

		</ul>

	</div>

</div>

<div class = "case_detail_nav">

	<ul class = "case_detail_nav_list">

		<li id="item1" class="selected">Case Notes</li>

		<li id="item2">Case Data</li>

		<li id="item3">Documents</li>

		<li id="item4">Events</li>

		<li id="item5">Messages</li>

		<li id="item6">Contacts</li>

		<li id="item7">Conflicts <span class="conflicts_number"></span></li>

	</ul>

</div>

<div class = "case_detail_panel">




</div>
