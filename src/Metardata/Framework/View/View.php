<?php

namespace Metardata\Framework\View;

use Twig_Environment;
use Twig_Loader_Filesystem;
use Twig\Extension\DebugExtension;

class View {

	protected $parts;
	protected $template;
	protected $twig;

	public function __construct($template, $parts=array()) {
		$this->template = $template;
		$this->parts = $parts;
		$loader = new Twig_Loader_Filesystem('src/Metardata/App/Templates');
		$this->twig = new Twig_Environment($loader, ['debug' => true]);
		$this->twig->addExtension(new DebugExtension());
	}

	public function setPart($key, $content){
		$this->parts[$key]=$content;
	}

	public function getPart($key){
		if(isset($this->parts[$key])) {
				return $this->parts[$key];
		} else {
			return null;
		}
	}

	public function render() {
		return $this->twig->render($this->template, $this->parts);
	}
}

?>
