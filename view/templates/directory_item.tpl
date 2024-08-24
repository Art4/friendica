{{*
  * Copyright (C) 2010-2024, the Friendica project
  * SPDX-FileCopyrightText: 2010-2024 the Friendica project
  *
  * SPDX-License-Identifier: AGPL-3.0-or-later
  *}}


<div class="directory-item lframe" id="directory-item-{{$entry.id}}">
	<div class="contact-photo-wrapper" id="directory-photo-wrapper-{{$entry.id}}">
		<div class="contact-photo" id="directory-photo-{{$entry.id}}">
			<a href="{{$entry.profile_link}}" class="directory-profile-link" id="directory-profile-link-{{$entry.id}}">
				<img class="directory-photo-img" src="{{$entry.photo}}" alt="{{$entry.alt_text}}" title="{{$entry.alt_text}}" />
			</a>
		</div>
	</div>

	<div class="contact-name" id="directory-name-{{$entry.id}}">{{$entry.name}}</div>
	<div class="contact-details">{{$entry.details}}</div>
</div>
