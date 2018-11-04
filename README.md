# agcwd

Adaptive Gamma Correction with Weighting Distribution in PHP with Imagick

## Example usage:
```
$t = new ImageAGCWD(new \Imagick('your-picture.png'));
$t->writeimage('output.png');
```
## Results:

Check my blog: https://jeanbruenn.info/2018/11/05/adaptive-gamma-correction-with-weighting-distribution-with-imagick-and-php/

## Notes:
* currently exports to sRGB colorspace regardless of the original colorspace
* you can set the adjustment parameter using ->setAdjustmentParameter(0.75), default is 0.5
