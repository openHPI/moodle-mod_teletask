<?php

/**
 * Description of VideoStream
 *
 * @author Rana
 * @link http://codesamplez.com/programming/php-html5-video-streaming-tutorial
 */



class VideoStream
{
    private $path = "";
    private $stream = "";
    private $buffer = 102400;
    private $start  = -1;
    private $end    = -1;
    private $size   = 0;

    function __construct($filepath) {
        $this->path = $filepath;
    }

    /**
     * Open stream
     */
    private function open() {
        if (is_file($this->path)) {
            if (!($this->stream = fopen($this->path, 'rb'))) {
                die('Could not open stream for reading');
            }
        } else {
            http_response_code(404);
            die();
        }
    }

    /**
     * Set proper header to serve the video content
     */
    private function setheader() {
        ob_get_clean();
        @ini_set('error_reporting', E_ALL & ~ E_NOTICE);
        header("Content-Type: video/mp4");
        header("Expires: ".gmdate('D, d M Y H:i:s', time()+2592000) . ' GMT');
        header("Last-Modified: ".gmdate('D, d M Y H:i:s', @filemtime($this->path)) . ' GMT' );
        $this->start = 0;
        $this->size  = filesize($this->path);
        $this->end   = $this->size - 1;
        header("Accept-Ranges: bytes");

        header("Keep-Alive: timeout=15, max=100");

        if (isset($_SERVER['HTTP_RANGE'])) {

            $c_start = $this->start;
            $c_end = $this->end;

            list(, $range) = explode('=', $_SERVER['HTTP_RANGE'], 2);
            if (strpos($range, ',') !== false) {
                header('HTTP/1.1 416 Requested Range Not Satisfiable');
                header("Content-Range: bytes $this->start-$this->end/$this->size");
                exit;
            }
            if ($range == '-') {
                $c_start = $this->size - substr($range, 1);
            } else {
                $range = explode('-', $range);
                $c_start = $range[0];
                $c_end = (isset($range[1]) && is_numeric($range[1])) ? $range[1] : $c_end;
            }
            $c_end = ($c_end > $this->end) ? $this->end : $c_end;
            if ($c_start > $c_end || $c_start > $this->size - 1 || $c_end >= $this->size) {
                header('HTTP/1.1 416 Requested Range Not Satisfiable');
                header("Content-Range: bytes $this->start-$this->end/$this->size");
                exit;
            }
            // Serve only 1MB of a video file to avoid blocking of a stream.
            $this->start = $c_start;
            $this->end = $c_end;

            $length = $this->end - $this->start + 1;
            fseek($this->stream, $this->start);
            header('HTTP/1.1 206 Partial Content');
            header("Content-Length: ".$length);
            header("Content-Range: bytes $this->start-$this->end/".$this->size);

        } else {

            header('HTTP/1.1 206 Partial Content');
            header("Content-Length: ".$this->size);
            header("Content-Range: bytes $this->start-$this->end/".$this->size);

        }
    }

    /**
     * close curretly opened stream
     */
    private function end() {
        fclose($this->stream);
        exit;
    }

    /**
     * perform the streaming of calculated range
     */
    private function stream() {
        $i = $this->start;
        set_time_limit(0);
        while (!feof($this->stream) && $i <= $this->end) {
            $bytesToRead = $this->buffer;
            if (($i+$bytesToRead) > $this->end) {
                $bytesToRead = $this->end - $i + 1;
            }
            $data = fread($this->stream, $bytesToRead);
            echo $data;
            flush();
            $i += $bytesToRead;
        }
    }

    /**
     * Start streaming video content
     */
    public function start() {
        $this->open();
        $this->setheader();
        $this->stream();
        $this->end();
    }
}