<?php
declare(strict_types=1);

namespace montest;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

final class MonTest extends TestCase
{
    public function testInitialRun(): void
    {
        $startTime = date_create();
        $startDir = getcwd();
        $workingDir = getcwd() . "/testfixtures_tmp";
        if (!is_dir($workingDir)) {
            mkdir($workingDir);
        }

        $cliScript = $workingDir . "/../src/mon.php";
        chdir($workingDir);

        $output = `php $cliScript`;

        //assert that the files are created
        for($i = 0; $i < 500; $i++) {
            $this->assertFileExists($workingDir . "/tmp-generated-data/$i/$i/$i/$i/dummy");
        }
        //assert that the content is not the same (only compare two files if they are not the same)
        $this->assertFileNotEquals($workingDir . "/tmp-generated-data/0/0/0/0/dummy", $workingDir . "/tmp-generated-data/1/1/1/1/dummy");
        //assert that the file size is correct (only check one file)
        $this->assertEquals(filesize($workingDir . "/tmp-generated-data/0/0/0/0/dummy"), 8192);
        //assert correct html output
        $this->assertMatchesRegularExpression('|<html><header></header><body><p>NEW</p><!-- maasmarker_version: 1 --><!-- maasmarker_read_avg_duration: \d+\.\d+(E-\d+)? --><!-- maasmarker_read_min_duration: \d+\.\d+(E-\d+)? --><!-- maasmarker_read_max_duration: \d+\.\d+(E-\d+)? --><!-- maasmarker_read_sum_duration: \d+\.\d+(E-\d+)? --><p>OK</p></body></html>|', $output);

        $this->assertFileExists($workingDir . "/info.txt");
        $infoFile = parse_ini_file($workingDir . "/info.txt");
        $this->assertEquals($infoFile["Version"], 1);
        $this->assertEqualsWithDelta(date_create($infoFile["Last-Run"]), $startTime, 5);

        chdir($startDir);
        $filesystem = new Filesystem();
        $filesystem->remove([$workingDir]);
    }

    public function testRecusringRun(): void
    {
        $startTime = date_create();
        $workingDir = getcwd() . "/testfixtures_fixed";

        $cliScript = $workingDir . "/../src/mon.php";
        chdir($workingDir);

        $output = `php $cliScript`;

        //assert that the files are created
        for($i = 0; $i < 500; $i++) {
            $this->assertFileExists($workingDir . "/tmp-generated-data/$i/$i/$i/$i/dummy");
        }
        //assert that the content is not the same (only compare two files if they are not the same)
        $this->assertFileNotEquals($workingDir . "/tmp-generated-data/0/0/0/0/dummy", $workingDir . "/tmp-generated-data/1/1/1/1/dummy");
        //assert that the file size is correct (only check one file)
        $this->assertEquals(filesize($workingDir . "/tmp-generated-data/0/0/0/0/dummy"), 8192);
        //assert correct html output
        $this->assertMatchesRegularExpression('|<html><header></header><body><!-- maasmarker_version: 1 --><!-- maasmarker_read_avg_duration: \d+\.\d+(E-\d+)? --><!-- maasmarker_read_min_duration: \d+\.\d+(E-\d+)? --><!-- maasmarker_read_max_duration: \d+\.\d+(E-\d+)? --><!-- maasmarker_read_sum_duration: \d+\.\d+(E-\d+)? --><p>OK</p></body></html>|', $output);

        $this->assertFileExists($workingDir . "/info.txt");
        $infoFile = parse_ini_file($workingDir . "/info.txt");
        $this->assertEquals($infoFile["Version"], 1);
        $this->assertEqualsWithDelta(date_create($infoFile["Last-Run"]), $startTime, 5);
    }
}