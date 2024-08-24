{{*
  * Copyright (C) 2010-2024, the Friendica project
  * SPDX-FileCopyrightText: 2010-2024 the Friendica project
  *
  * SPDX-License-Identifier: AGPL-3.0-or-later
  *}}
<div class="generic-page-wrapper">
	<h3>{{$l10n.header}}</h3>

	<p id="tag-remove-desc">{{$l10n.desc}}</p>

	<form id="tagrm" action="post/{{$item_id}}/tag/remove?return={{$return}}" method="post">
		<ul>
{{foreach $tag_checkboxes as $tag_checkbox}}
			<li>
                {{include file="field_checkbox.tpl" field=$tag_checkbox}}
			</li>
{{/foreach}}
		</ul>
		<p>
			<button type="submit" id="tagrm-submit" class="btn btn-primary" name="submit">{{$l10n.remove}}</button>
			<button type="submit" id="tagrm-submit" class="btn" name="cancel">{{$l10n.cancel}}</button>
		</p>
	</form>
</div>
