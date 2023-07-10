<?php

$file_size = 1024*8;
$base_dir = "tmp-generated-data";
$output_file = "info.txt";
$count = 500;
$version = 1;

function get_rand_data($size) {
    $f = fopen("/dev/urandom","r");
    $data = fread($f, $size);
    fclose($f);
    return $data;
}

function gen_dir_path($nr) {
    global $base_dir;
    return $base_dir . "/" . $nr . "/" . $nr . "/" . $nr . "/" . $nr . "/";
}

function gen_file_path($nr) {
    return gen_dir_path($nr) . "dummy";
}

echo("<html><header></header><body>");

if(!is_dir($base_dir)) {
    echo("<p>NEW</p>");
    for($i = 0; $i < $count; $i++) {
        mkdir(gen_dir_path($i), 0777, true);
        $fd = fopen(gen_file_path($i),"w");
        fwrite($fd, get_rand_data($file_size));
        fclose($fd);
    }
}

// multi file read
$max_duration = 0;
$min_duration = PHP_FLOAT_MAX;
$sum_duration = 0;
for($i = 0; $i < $count; $i++) {
    $ts_start = microtime(true);

    $fd = fopen(gen_file_path($i),"r");
    fread($fd, $file_size);
    fclose($fd);

    $duration = microtime(true) - $ts_start;
    $sum_duration += $duration;

    $max_duration = ($max_duration < $duration) ? $duration : $max_duration;
    $min_duration = ($min_duration > $duration) ? $duration : $min_duration;;
}
$avg_duration = $sum_duration / $count;


// small write
$fd = fopen($output_file,"w");
fwrite($fd, "Version= $version\nLast-Run= " . date_format(date_create(), 'c') . "\n");
fclose($fd);


echo("<!-- maasmarker_version: $version -->");
echo("<!-- maasmarker_read_avg_duration: $avg_duration -->");
echo("<!-- maasmarker_read_min_duration: $min_duration -->");
echo("<!-- maasmarker_read_max_duration: $max_duration -->");
echo("<!-- maasmarker_read_sum_duration: $sum_duration -->");

echo("<p>OK</p>");
echo("</body></html>");
?>