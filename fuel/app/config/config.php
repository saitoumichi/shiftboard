<?php
return [
  "locale"   => ["ja_JP.UTF-8","C.UTF-8","C"],
  "language" => "ja",
  "index_file" => false,
  "security" => [
    "output_filter" => "Security::htmlentities",
    "uri_filter"    => ["Security::xss_clean"],
    "input_filter"  => [],
    "whitelisted_classes" => [],
  ],
];
