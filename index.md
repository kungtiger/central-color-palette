---
layout: default
---

## get_colors( [$options] )

Get the colors of the central palette.

Option | Type | Default | Description
------ | ---- | ------- | -----------
`alpha` | bool | `false` | If `true` and a colors of the palette has a transparency less than 100 the color will be returned inCSS `rgba()` notation
`min` | bool or int | `false` | The number of colors that should at least be returned
`pad` | string | `"#FFFFFF"` | If the palette has less than `min` colors pad by this
`hash` | bool | `true` | Whether to prepent each color by an hash (`#`) or not
`default` | mixed | empty array | If the palette is empty return this instead

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
        "status" => kt_Central_Palette::COLOR_ACTIVE,
        "index" => 9,
    ),
    ...
));
```

Comes in two flavors: Either pass an array of colors in in hexadecimal notation, or if you need more refinement pass an array of arrays. These arrays need to have at least the `color` entry and all other entries are optional.  
Any invalid color will be silently ignored. Valid colors are defined by the regular expression `#?{[0-9a-fA-F]{3}|[0-9a-fA-F]{6}}`.

`name` is pretty self-explanatory. If not set an empty string is assumed.

`alpha` needs to be a integer between 0 and 100. If not set 100 is assumed.

The `status` option can be set by two constants:

| `kt_Central_Palette::COLOR_ACTIVE` | The color is active |
| `kt_Central_Palette::COLOR_INACTIVE` | The color is inactive |

If `status` is not set `kt_Central_Palette::COLOR_ACTIVE` is assumed.

The `index` option sets the internal number assigned to each color. Unless you really know what you're doing just ignore it.