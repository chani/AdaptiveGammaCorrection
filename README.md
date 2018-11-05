# Adaptive Gamma Correction

1. AGCWD Adaptive Gamma Correction with Weighting Distribution in PHP with Imagick
2. IAGCWD Adaptive Gamma Correction with Weighting Distribution modified to enhance brightness-distorted pictures

## Example usage:

```
// agcwd
$t = new ImageAGCWD(new \Imagick('your-picture.png'));
$t->writeimage('output.png');

// iagcwd
$t = new ImageIAGCWD(new \Imagick('your-picture.png'));
$t->writeimage('output.png');
```

## Results:

* AGCWD: https://jeanbruenn.info/2018/11/05/adaptive-gamma-correction-with-weighting-distribution-with-imagick-and-php/
* IAGCWD: https://jeanbruenn.info/2018/11/06/adaptive-gamma-correction-for-brightness-distorted-images-with-imagick-and-php/

## Notes:

* currently exports to sRGB colorspace regardless of the original colorspace
* you can set the adjustment parameter using ->setAdjustmentParameter(0.75), default is 0.5
* iagcwd has hardcoded adjustment parameter currently (0.25 for bright images, 0.75 for dimmed)
