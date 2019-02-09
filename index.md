---
layout: default
---

## get_colors( [$options] )

Get the colors of the central palette.

Option | Type | Default | Description
------ | ---- | ------- | -----------
`alpha` | bool | `false` | If `true` and a colors of the palette has a transparency less than 100 the color will be returned in CSS `rgba()` notation
`min` | bool or int | `false` | The number of colors that should at least be returned
`pad` | string | `"#FFFFFF"` | If the palette has less than `min` colors pad by this
`hash` | bool | `true` | Whether to prepent each color by an hash (`#`) or not
`default` | mixed | empty array | If the palette is empty return this instead

## get_palette( [$options] )

Get the central palette.

Option | Type | Default | Description
------ | ---- | ------- | -----------
`status` | int or array | `false` | Filter the colors by status.
`chunk` | false or int | `false` | Chunk palette into columns of constant size.
`pad` | mixed | black | Pad the last column by this to the length of `chunk`.

## set_palette( $colors )

Set the central palette.

```php
kt_Central_Palette::instance()->set_palette(array( "#222299", "6DE", "#777" ... ));

kt_Central_Palette::instance()->set_palette(array(
    array(
        "color" => "#222299",
        "name" => "Night",
        "alpha" => 80,
        "status" => kt_Central_Palette::COLOR_ACTIVE,
        "index" => 9,
    ),
    ...
));
```

Comes in two flavors: Either pass an array of colors in in hexadecimal notation, or if you need more refinement pass an array of arrays. These arrays need to have at least the `color` entry and all other entries are optional.  
Any invalid color will be silently ignored.

Option | Type | Default | Description
------ | ---- | ------- | -----------
`color` | string | none | Valid colors are defined by the regular expression `#?{[0-9a-fA-F]{3}|[0-9a-fA-F]{6}}`
`name` | string | empty string | Pretty self-explanatory
`alpha` | int | 100 | An integer between 0 and 100
`status` | int | `kt_Central_Palette::COLOR_ACTIVE` | `kt_Central_Palette::COLOR_ACTIVE` or `kt_Central_Palette::COLOR_INACTIVE`
`index` | int | auto | Internal number assigned to each color. Unless you really know what you're doing just ignore it.

## float2hex( $float )

Convert a float to a string in hexadecimal notation.

```php
kt_Central_Palette::instance()->float2hex(0);    // 00
kt_Central_Palette::instance()->float2hex(0.3);  // 4C
kt_Central_Palette::instance()->float2hex(1);    // FF
```

## hex2rgb( $hex )

Convert a string in hexadecimal notation to a RGB vector.

```php
kt_Central_Palette::instance()->hex2rgb("#FF0000");  // [ 255, 0, 0 ]
kt_Central_Palette::instance()->hex2rgb("060");      // [ 0, 102, 0 ]
kt_Central_Palette::instance()->hex2rgb("6CD240");   // [ 108, 210, 64 ]
```

## hex2rgba( $hex, $alpha )

Convert a string in hexadecimal notation and a alpha to CSS RGBA notation.

```php
kt_Central_Palette::instance()->hex2rgba("#FF0000", 80);  // "rgba(255,0,0,0.8)"
kt_Central_Palette::instance()->hex2rgba("060", 50);      // "rgba(0,102,0,0.5)"
kt_Central_Palette::instance()->hex2rgba("6CD240", 100);  // "rgba(108,210,64,1)"
```

## int2hex( $int )

Convert a integer to a string in hexadecimal notation.

```php
kt_Central_Palette::instance()->int2hex(0);     // 00
kt_Central_Palette::instance()->int2hex(76);    // 4C
kt_Central_Palette::instance()->int2hex(255);   // FF
```

## rgb2hex( $rgb [, $as_floats [, $prepend_hash ]] )

This method accepts two additional optional arguments.

If you pass `true` as second argument the vector components are interpreted as floats between 0 and 1.

```php
kt_Central_Palette::instance->rgb2hex(array(0, 0, 0), true);        // #000000
kt_Central_Palette::instance->rgb2hex(array(0.5, 0.5, 0.5), true);  // #7F7F7F
kt_Central_Palette::instance->rgb2hex(array(1, 1, 1), true);        // #FFFFFF
```

If you pass a boolean as third argument the resulting string will be prepended by a hash (`#`) or not.

```php
kt_Central_Palette::instance->rgb2hex(array(51, 0, 204), false, false);  // 3300CC
kt_Central_Palette::instance->rgb2hex(array(.2, 0, .8), true, false);    // 3300CC
```
