{{*
  * Copyright (C) 2010-2024, the Friendica project
  * SPDX-FileCopyrightText: 2010-2024 the Friendica project
  *
  * SPDX-License-Identifier: AGPL-3.0-or-later
  *}}
{{foreach $rows as $images}}
	<div class="masonry-row" style="height: {{$images->getHeightRatio()}}%">
        {{foreach $images as $image}}
            {{* The absolute pixel value in the calc() should be mirrored from the .imagegrid-row column-gap value *}}
            {{include file="content/image/single_with_height_allocation.tpl"
	            image=$image
	            allocated_height="calc(`$image->heightRatio * $image->widthRatio / 100`% - 5px / `$column_size`)"
	            allocated_width="`$image->widthRatio`%"
            }}
        {{/foreach}}
	</div>
{{/foreach}}
