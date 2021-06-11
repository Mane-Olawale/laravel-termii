<?php

namespace ManeOlawale\Laravel\Termii\Messages;

class TermiiMessage extends Message
{

    /**
     * Array of content lines.
     *
     * @var array
     */
    public $lines = [];

    /**
     * Add a line of text to the message content.
     *
     * @param  string  $text
     * @return $this
     */
    public function line( string $text = null)
    {
        $this->lines[] = $text;

        return $this;
    }

    public function getContent() : string
    {
        $lines = (($this->content)? "\n" : "").implode( "\n",$this->lines);

        return trim($this->content.$lines);
    }

}
