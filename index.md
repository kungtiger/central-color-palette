---
layout: default
---

## get_colors( [$options] )

Get the colors of the central palette.

## get_palette( [$options] )

Get the central palette.

## set_palette( $colors )

Set the central palette.

```php
kt_Central_Palette::instance()->set_palette(array( "#222299", "6DE", "#777" ... ));
kt_Central_Palette::instance()->set_palette(array(
    array(
        "color" => "#222299",
        "name" => "Night",
        "alpha" => 80,
        "index" => 9,
        "status" => kt_Central_Palette::COLOR_ACTIVE,
    ),
    ...
));
```

This method requires one argument and it can either be an array of colors in hexadecimal notation, or if you need more refinement pass an array of arrays.