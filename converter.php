<?php
/**
 * Flaming Shame Raster to SVG converter
 *
 * @author  Eric Meyer, Amelia Bellamy-Royds, Robin Cafolla, Neal Brooks
 * @arg	 string url	  Takes a single string url or path to local image to
 *						  convert from raster to SVG.
 */

class px2svg
{

	private $image;


	/**
	 * Get an image from a URL or file path
	 *
	 * @param string $url Url or path to local file
	 * @return GDImageIdentifier
	 */
	public function loadImage($path)
	{

		if (!$this->localFileExists($path) && !$this->isUrl($path)) {
			throw new \LogicException('Supplied URL / path is invalid.');
		}

		$this->image = imagecreatefromstring(file_get_contents($path));

		return $this;
	}

	private function isUrl($url) {
		return filter_var($url, FILTER_VALIDATE_URL);
	}

	private function localFileExists($path) {
		return file_exists($path);
	}

	/**
	 * Generates svg from raster
	 *
	 * @param GDImageIdentifier $img Raster image to convert to svg
	 * @return string				   SVG xml
	 */

	public function generateSVG() {
		$width = imagesx($this->image); // image width
		$height = imagesy($this->image); // image height

		$svgv = "";
		for ($x = 0; $x < $width; $x++) {
			for ($y = 0; $y < $height; $y = $y + $number_of_consecutive_pixels) {
				$color_at_position = imagecolorat($this->image, $x, $y);
				$number_of_consecutive_pixels = 1;

				while (
					($y + $number_of_consecutive_pixels < $height) &&
					($color_at_position == imagecolorat($this->image, $x, ($y + $number_of_consecutive_pixels)))
				) {
					$number_of_consecutive_pixels++;
				}

				$rgb = imagecolorsforindex($this->image, $color_at_position);
				$color = "rgb($rgb[red],$rgb[green],$rgb[blue])";

				if ($rgb["alpha"] && ($rgb["alpha"] < 128)) {
					$alpha = (128 - $rgb["alpha"]) / 128;
					$color .= "\" fill-opacity=\"$alpha";
				}

				$svgv .= "<rect width=\"1\" x=\"$x\" height=\"$number_of_consecutive_pixels\" y=\"$y\" fill=\"$color\"/>\n";
			}
		}


		$number_of_consecutive_pixels = 1; //reset number of consecutive pixels
		$svgh = "";
		for ($y = 0; $y < $height; $y++) {
			for ($x = 0; $x < $width; $x = $x + $number_of_consecutive_pixels) {
				$color_at_position = imagecolorat($this->image, $x, $y);
				$number_of_consecutive_pixels = 1;

				while(
					($x + $number_of_consecutive_pixels < $width) &&
					($color_at_position == imagecolorat($this->image, ($x + $number_of_consecutive_pixels), $y))
				) {
					$number_of_consecutive_pixels++;
				}

				$rgb = imagecolorsforindex($this->image, $color_at_position);
				$color = "rgb($rgb[red],$rgb[green],$rgb[blue])";

				if ($rgb["alpha"] && ($rgb["alpha"] < 128 )) {
					$alpha = (128 - $rgb["alpha"]) / 128;
					$color .= "\" fill-opacity=\"$alpha";
				}

				$svgh .= "<rect x=\"$x\" y=\"$y\" width=\"$number_of_consecutive_pixels\" height=\"1\" fill=\"$color\"/>\n";
			}
		}

		if (strlen($svgh) < strlen($svgv)) $svg = $svgh; else $svg = $svgv;

		return "<svg xmlns=\"http://www.w3.org/2000/svg\" shape-rendering=\"crispEdges\">" . $svg . "</svg>";
	}


}
