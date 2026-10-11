<?php
/**
 * Title: EyeDonation Hero
 * Slug: eyedonation/hero
 * Categories: featured
 * Description: Editable section; select supplied media and approved destinations before publishing.
 */
$blocks=parse_blocks(ed_latest_homepage_pattern_content());
echo serialize_block($blocks[0]);
