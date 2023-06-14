<?php

class HtmlHelper
{
    protected
        $reachedLimit = false,
        $totalLen = 0,
        $maxLen = 25,
        $toRemove = [];
    
    public static function cut($html, $maxLen = 25)
    {
        $html = "<!DOCTYPE html><html>" .
            "<head>" .
            "<meta http-equiv=\"content-type\" content=\"text/html; charset=utf-8\"/>" .
            "</head>" .
            "<body>" .
            $html .
            "</body>" .
            "</html>";
        
        $dom = new \DOMDocument("1.0", "UTF-8");
        $dom->loadHTML($html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        
        $instance = new static();
        $toRemove = $instance->walk($dom, $maxLen);
        
        // remove any nodes that exceed limit
        foreach ($toRemove as $child) {
            $child->parentNode->removeChild($child);
        }
        
        $html = $dom->saveHTML();
        if(preg_match("/<body>(.*)<\/body>/msui", $html, $matches)) {
            $html = $matches[1];
        }
        
        return $html;
    }
    
    protected function walk(\DOMNode $node, $maxLen)
    {
        if ($this->reachedLimit) {
            $this->toRemove[] = $node;
        } else {
            // only text nodes should have text,
            // so do the splitting here
            if ($node instanceof \DOMText) {
                $this->totalLen += $nodeLen = utf8_strlen($node->nodeValue);
                
                // use mb_strlen / mb_substr for UTF-8 support
                if ($this->totalLen > $maxLen) {
                    $node->nodeValue = utf8_substr($node->nodeValue, 0, $nodeLen - ($this->totalLen - $maxLen)) . ' ...';
                    $this->reachedLimit = true;
                }
            }
            
            // if node has children, walk its child elements
            if (isset($node->childNodes)) {
                foreach ($node->childNodes as $child) {
                    $this->walk($child, $maxLen);
                }
            }
        }
        
        return $this->toRemove;
    }
}

?>