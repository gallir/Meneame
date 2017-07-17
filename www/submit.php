<?php
header("Status: 301 Moved Permanently");
header('Location: /submit?'.$_SERVER['QUERY_STRING']);
