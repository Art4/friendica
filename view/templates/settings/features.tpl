{{*
  * Copyright (C) 2010-2024, the Friendica project
  * SPDX-FileCopyrightText: 2010-2024 the Friendica project
  *
  * SPDX-License-Identifier: AGPL-3.0-or-later
  *}}
<h1>{{$title}}</h1>

<form action="settings/features" method="post" autocomplete="off">
	<input type="hidden" name="form_security_token" value="{{$form_security_token}}">

	{{foreach $features as $f}}
		<h2 class="settings-heading"><a href="javascript:;">{{$f.0}}</a></h2>
		<div class="settings-content-block">

			{{foreach $f.1 as $fcat}}
				{{include file="field_checkbox.tpl" field=$fcat}}
			{{/foreach}}
			<div class="settings-submit-wrapper">
				<input type="submit" name="submit" class="settings-features-submit" value="{{$submit}}"/>
			</div>
		</div>
	{{/foreach}}
</form>
