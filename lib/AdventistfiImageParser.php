<?php

class AdventistfiRSSImageParser {

    public function get_image($html) {
        $doc = new DOMDocument();
        $doc->loadHTML($html);
        $img = $doc->getElementsByTagName('img')->item(0);
        $src = $img->getAttribute('src');
        return $src;
    }

} 