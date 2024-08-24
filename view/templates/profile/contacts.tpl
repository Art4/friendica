{{*
  * Copyright (C) 2010-2024, the Friendica project
  * SPDX-FileCopyrightText: 2010-2024 the Friendica project
  *
  * SPDX-License-Identifier: AGPL-3.0-or-later
  *}}
<div class="generic-page-wrapper">
	{{include file="section_title.tpl"}}

{{if $desc}}
	<p>{{$desc nofilter}}</p>
{{/if}}

	{{include file="page_tabs.tpl" tabs=$tabs}}

{{if $contacts}}
	<div id="viewcontact_wrapper-{{$id}}">
	{{foreach $contacts as $contact}}
		{{include file="contact/entry.tpl"}}
	{{/foreach}}
	</div>
{{else}}
	<div class="alert alert-info" role="alert">{{$noresult_label}}</div>
{{/if}}

	<div class="clear"></div>
	<div id="view-contact-end"></div>

	{{$paginate nofilter}}
</div>
