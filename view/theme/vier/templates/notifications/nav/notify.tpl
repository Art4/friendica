{{*
  * Copyright (C) 2010-2024, the Friendica project
  * SPDX-FileCopyrightText: 2010-2024 the Friendica project
  *
  * SPDX-License-Identifier: AGPL-3.0-or-later
  *}}
<li class="notification-{{if !$notify.seen}}un{{/if}}seen" onclick="location.href='{{$notify.href}}';">
	<div class="notif-entry-wrapper">
		<div class="notif-photo-wrapper"><a href="{{$notify.contact.url}}"><img data-src="{{$notify.contact.photo}}" loading="lazy"></a></div>
		<div class="notif-desc-wrapper">
            {{$notify.richtext nofilter}}
			<div><time class="notif-when" title="{{$notify.localdate}}">{{$notify.ago}}</time></div>
		</div>
	</div>
</li>
