{{*
  * Copyright (C) 2010-2024, the Friendica project
  * SPDX-FileCopyrightText: 2010-2024 the Friendica project
  *
  * SPDX-License-Identifier: AGPL-3.0-or-later
  *}}

<div class="notif-item {{if !$item_seen}}unseen{{/if}}">
	<a href="{{$notification.link}}"><img src="{{$notification.image}}" class="notif-image">{{$notification.text nofilter}} <span class="notif-when">{{$notification.ago}}</span></a>
</div>
