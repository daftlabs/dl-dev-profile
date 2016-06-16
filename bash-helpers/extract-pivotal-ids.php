<?php

preg_match_all('/\[(\((Finishes|Fixes|Delivers)\) )?#[0-9]+\]/', file_get_contents('php://stdin'), $matches);
echo implode("\n", $matches[0]) . "\n";
