<?php
$f='app/Support/ExamSectionOptimizer.php'; $s=file_get_contents($f);
$a="        \$text = str_replace('</tr>', \"\n\", \$text);";
if(substr_count($s,$a)!==1){echo "MISS\n";exit(1);}
file_put_contents($f,str_replace($a,"        \$text = preg_replace('/<tr\b/i', \"\n<tr\", \$text);   // a row starts on its own line, so text before the table cannot join it\n        \$text = str_replace('</tr>', \"\n\", \$text);",$s)); echo "ok\n";
