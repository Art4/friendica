{{*
  * Copyright (C) 2010-2024, the Friendica project
  * SPDX-FileCopyrightText: 2010-2024 the Friendica project
  *
  * SPDX-License-Identifier: AGPL-3.0-or-later
  *}}


<h1>{{$header}}</h1>

{{if $tabs }}{{include file="common_tabs.tpl"}}{{/if}}

<div class="notif-network-wrapper">
	{{* The "show ignored" link *}}
	{{if $showLink}}<a href="{{$showLink.href}}" id="notifications-show-hide-link">{{$showLink.text}}</a>{{/if}}

	{{* The notifications *}}
	{{if $notifications}}
	{{foreach $notifications as $notification}}
		{{$notification nofilter}}
	{{/foreach}}
	{{/if}}

	{{* If no notifications messages available *}}
	{{if $noContent}}
		<div class="notification_nocontent">{{$noContent}}</div>
	{{/if}}

	{{* The pager *}}
	{{$paginate nofilter}}
</div>
