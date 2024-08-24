{{*
  * Copyright (C) 2010-2024, the Friendica project
  * SPDX-FileCopyrightText: 2010-2024 the Friendica project
  *
  * SPDX-License-Identifier: AGPL-3.0-or-later
  *}}
<h1>{{$title}}</h1>

{{foreach $addon_settings_forms as $addon => $addon_settings_form}}

<form action="settings/addons/{{$addon}}" method="post" autocomplete="off">
	<input type="hidden" name="form_security_token" value="{{$form_security_token}}">
	{{$addon_settings_form nofilter}}
</form>

{{foreachelse}}

<p>{{$no_addon_settings_configured}}</p>

{{/foreach}}
