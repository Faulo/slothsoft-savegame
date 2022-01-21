<?php
declare(strict_types = 1);
namespace Slothsoft\Savegame;

use Slothsoft\Savegame\Script\Parser;
use RangeException;

class Converter {

    /**
     *
     * @return \Slothsoft\Savegame\Converter
     */
    public static function getInstance() {
        static $instance;
        if (! $instance) {
            $instance = new Converter();
        }
        return $instance;
    }

    public function encodeInteger($val, $size = 1) {
        $val = (int) $val;
        $ret = pack('N', $val);
        $ret = substr($ret, - $size);
        return $ret;
    }

    public function encodeSignedInteger($val, $size = 1) {
        return $this->encodeInteger($val, $size);
    }

    public function encodeString($val, $size = 1, $encoding = '') {
        $val = (string) $val;
        $val = trim($val);
        if ($encoding) {
            $val = mb_convert_encoding($val, $encoding, 'UTF-8');
        }
        $ret = substr($val, 0, $size);
        $ret = str_pad($ret, $size, "\0");
        return $ret;
    }

    public function encodeBinary($val) {
        return hex2bin(preg_replace('~\s+~', '', $val));
    }

    public function encodeScript($val) {
        $parser = new Parser();
        return $parser->code2binary($val);
    }

    public function decodeInteger(string $val, int $size = 1): int {
        static $unpackList = [
            [],
            [],
            [],
            [],
            []
        ];

        assert($size >= 0 and $size <= 4, '$size must be 1/2/3/4');

        $key = bin2hex($val);

        if (! isset($unpackList[$size][$key])) {
            switch ($size) {
                case 1:
                    $format = 'C';
                    break;
                case 2:
                    $format = 'n';
                    break;
                case 3:
                    $val = "\0" . $val;
                    $format = 'N';
                    break;
                case 4:
                    $format = 'N';
                    break;
                default:
                    throw new RangeException('unknown integer size: ' . $size);
            }
            $unpackList[$size][$key] = unpack($format, $val)[1];
        }

        return $unpackList[$size][$key];
    }

    public function decodeSignedInteger(string $val, int $size = 1): int {
        $ret = $this->decodeInteger($val, $size);
        if ($ret > $this->pow256($size) / 2) {
            $ret -= $this->pow256($size);
        }
        return $ret;
    }

    public function decodeString(string $val, int $size = 1, string $encoding = ''): string {
        $ret = '';
        $size = min($size, strlen($val));
        for ($i = 0; $i < $size and $val[$i] !== "\0"; $i ++) {
            $ret .= $val[$i];
        }
        return $encoding === '' ? $ret : mb_convert_encoding($ret, 'UTF-8', $encoding);
    }

    public function decodeBinary($val) {
        return chunk_split(bin2hex($val), 2, ' ');
    }

    public function decodeScript($val) {
        $parser = new Parser();
        return $parser->binary2code($val);
    }

    public function pow2(int $size): int {
        static $powList = [];
        if (! isset($powList[$size])) {
            $powList[$size] = pow(2, $size);
        }
        return $powList[$size];
    }

    public function pow256(int $size): int {
        static $powList = [];
        if (! isset($powList[$size])) {
            $powList[$size] = pow(256, $size);
        }
        return $powList[$size];
    }
}