{{*
  * Copyright (C) 2010-2024, the Friendica project
  * SPDX-FileCopyrightText: 2010-2024 the Friendica project
  *
  * SPDX-License-Identifier: AGPL-3.0-or-later
  *}}
<div id="adminpage">
	<h1>{{$title}}</h1>

	<form action="{{$baseurl}}/admin/features" method="post" autocomplete="off">
	<input type="hidden" name="form_security_token" value="{{$form_security_token}}">

	{{foreach $features as $g => $f}}
	<h2 class="settings-heading"><a href="javascript:;">{{$f.0}}</a></h2>

	<div class="settings-content-block">
		{{foreach $f.1 as $fcat}}
			<div class="settings-block">
			{{include file="field_select.tpl" field=$fcat}}
			</div>
		{{/foreach}}

		<div class="settings-submit-wrapper">
			<input type="submit" name="submit" class="settings-features-submit" value="{{$submit}}" />
		</div>
	</div>
	{{/foreach}}

	</form>
</div>
