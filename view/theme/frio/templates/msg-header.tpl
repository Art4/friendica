{{*
  * Copyright (C) 2010-2024, the Friendica project
  * SPDX-FileCopyrightText: 2010-2024 the Friendica project
  *
  * SPDX-License-Identifier: AGPL-3.0-or-later
  *}}

<script type="text/javascript">
	$("#comment-edit-text-input").editor_autocomplete(baseurl + '/search/acl');

	$(document).ready(function() {
		$("#comment-edit-text-input").bbco_autocomplete('bbcode');
		$('#mail-conversation').perfectScrollbar();
		$('#message-preview').perfectScrollbar();
		// Scroll to the bottom of the mail conversation.
		var $el = $('#mail-conversation');
		if ($el.length) {
			$el.scrollTop($el.get(0).scrollHeight);
		}
	});
</script>
