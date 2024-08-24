{{*
  * Copyright (C) 2010-2024, the Friendica project
  * SPDX-FileCopyrightText: 2010-2024 the Friendica project
  *
  * SPDX-License-Identifier: AGPL-3.0-or-later
  *}}


{{foreach $mails as $mail}}
	{{include file="mail_conv.tpl"}}
{{/foreach}}

{{if $canreply}}
{{include file="prv_message.tpl"}}
{{else}}
{{$unknown_text}}
{{/if}}
