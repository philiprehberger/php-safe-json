<?php

declare(strict_types=1);

namespace PhilipRehberger\SafeJson\Tests;

use PhilipRehberger\SafeJson\Exceptions\JsonDecodeException;
use PhilipRehberger\SafeJson\SafeJson;
use PhilipRehberger\SafeJson\StreamDecoder;
use PHPUnit\Framework\TestCase;

class StreamDecoderTest extends TestCase
{
    private string $tempDir;

    protected function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir().'/safe-json-test-'.uniqid();
        mkdir($this->tempDir);
    }

    protected function tearDown(): void
    {
        $files = glob($this->tempDir.'/*');

        if ($files !== false) {
            foreach ($files as $file) {
                unlink($file);
            }
        }

        rmdir($this->tempDir);
    }

    public function test_decode_simple_array(): void
    {
        $file = $this->writeFile('[1, 2, 3]');

        $result = iterator_to_array(StreamDecoder::decodeStream($file));

        $this->assertSame([0 => 1, 1 => 2, 2 => 3], $result);
    }

    public function test_correct_count(): void
    {
        $items = array_map(fn (int $i) => ['id' => $i], range(1, 10));
        $file = $this->writeFile(json_encode($items));

        $count = 0;

        foreach (StreamDecoder::decodeStream($file) as $item) {
            $count++;
        }

        $this->assertSame(10, $count);
    }

    public function test_nested_objects(): void
    {
        $data = [
            ['name' => 'Alice', 'address' => ['city' => 'Vienna']],
            ['name' => 'Bob', 'address' => ['city' => 'Berlin']],
        ];
        $file = $this->writeFile(json_encode($data));

        $result = iterator_to_array(StreamDecoder::decodeStream($file));

        $this->assertSame('Alice', $result[0]['name']);
        $this->assertSame('Vienna', $result[0]['address']['city']);
        $this->assertSame('Bob', $result[1]['name']);
        $this->assertSame('Berlin', $result[1]['address']['city']);
    }

    public function test_empty_array(): void
    {
        $file = $this->writeFile('[]');

        $result = iterator_to_array(StreamDecoder::decodeStream($file));

        $this->assertSame([], $result);
    }

    public function test_missing_file_throws(): void
    {
        $this->expectException(JsonDecodeException::class);
        $this->expectExceptionMessage('File not found');

        iterator_to_array(StreamDecoder::decodeStream('/nonexistent/path/file.json'));
    }

    public function test_safe_json_decode_stream(): void
    {
        $file = $this->writeFile('[{"id":1},{"id":2}]');

        $result = iterator_to_array(SafeJson::decodeStream($file));

        $this->assertCount(2, $result);
        $this->assertSame(1, $result[0]['id']);
        $this->assertSame(2, $result[1]['id']);
    }

    private function writeFile(string $content): string
    {
        $path = $this->tempDir.'/'.uniqid().'.json';
        file_put_contents($path, $content);

        return $path;
    }
}
