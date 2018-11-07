<?php
/**
 * Class ImageAGCIE
 *
 * PHP implementation of an Adaptive Gamma Correction for image enhancement
 *
 * Reference:
 * - Rahman, Shanto & Rahman, Md. Mostafijur & Abdullah-Al-Wadud, M & Al-Quaderi, Golam Dastegir &
 *   Shoyaib, Mohammad. (2016). An adaptive gamma correction for image enhancement. EURASIP Journal
 *   on Image and Video Processing. 35. 10.1186/s13640-016-0138-1.
 *
 * @author Jean-Michel Bruenn <himself@jeanbruenn.info>
 * @copyright 2018 <himself@jeanbruenn.info>
 * @license https://opensource.org/licenses/MIT The MIT License
 * @see https://github.com/chani/AdaptiveGammaCorrection
 * @see https://jeanbruenn.info/2018/11/06/another-adaptive-gamma-correction-implementation/
 */
class ImageAGCIE extends ImageAGC
{
    private $contrastClassifier = 3;

    /**
     * @return int
     */
    public function getContrastClassifier()
    {
        return $this->contrastClassifier;
    }

    /**
     * @param int $contrastClassifier
     */
    public function setContrastClassifier($contrastClassifier)
    {
        $this->contrastClassifier = $contrastClassifier;
    }

    /**
     * @return \Imagick
     */
    protected function transform()
    {
        $data = $this->b->getImageChannelMean(\Imagick::CHANNEL_ALL);
        $standardDeviation = $data['standardDeviation'] / $this->b->getQuantum();
        $mean = $data['mean'] / $this->b->getQuantum();

        $subClass = ($mean >= 0.5) ? 'b' : 'd';
        $class = (4 * $standardDeviation <= 1 / $this->contrastClassifier) ? 'lc' . $subClass : 'hc' . $subClass;

        if ($class == 'lcb' || $class == 'lcd') {
            $y = -log($standardDeviation, 2);
        } else {
            $y = exp((1 - ($mean + $standardDeviation)) / 2);
        }
        $h = ((0.5 - $mean) <= 0) ? 0 : 1;
        $m = pow($mean, $y);

        $imageIterator = $this->t->getPixelIterator();
        foreach ($imageIterator as $pixels) {
            /** @var $pixel \ImagickPixel * */
            foreach ($pixels as $pixel) {
                $color = $pixel->getcolor(true);
                $l = $color['b'];
                $in = pow($l, $y);

                if ($class == 'lcb') {
                    $value = $in;
                } elseif ($class == 'lcd') {
                    $value = $in / ($in + ((1 - $in) * $m));
                } else {
                    $k = $in + ((1 - $in) * $m);
                    $c = 1 / (1 + ($h * ($k - 1)));
                    $value = $c * $in;
                }

                $pixel->setColorValue(\Imagick::COLOR_RED, $value);
                $pixel->setColorValue(\Imagick::COLOR_BLUE, $value);
                $pixel->setColorValue(\Imagick::COLOR_GREEN, $value);
            }
            $imageIterator->syncIterator();
        }
        return $this->combine();
    }
}