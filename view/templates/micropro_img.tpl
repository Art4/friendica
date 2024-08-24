{{*
  * Copyright (C) 2010-2024, the Friendica project
  * SPDX-FileCopyrightText: 2010-2024 the Friendica project
  *
  * SPDX-License-Identifier: AGPL-3.0-or-later
  *}}

<div class="contact-block-div{{if $class}} {{$class}}{{/if}}">
	<a class="contact-block-link{{if $class}} {{$class }}{{/if}}{{if $sparkle}} sparkle{{/if}}{{if $click}} fakelink{{/if}}"{{if $redir}} target="redir"{{/if}}{{if $url}} href="{{$url}}"{{/if}}{{if $click}} onclick="{{$click}}"{{/if}}>
		<img class="contact-block-img{{if $class}} {{$class }}{{/if}}{{if $sparkle}} sparkle{{/if}}" src="{{$photo}}" title="{{$title}}" alt="{{$name}}"/>
	</a>
</div>
