# [smi](http://nevvix.github.io/smi)

## social media icons &amp; links

The goal is for anyone to use this code to create a custom social media icon bar without the `iframe`s used by social media widgets.

It's using the CSS sprite technique to display social media icons from the 100 Social Media Icons image in the [Smashing Magazine article about Simple Icons](http://www.smashingmagazine.com/2013/03/10/free-brand-icons-color-style-guides-icons).

Loading one sprite image creates only one HTTP request and avoids all the HTTP requests caused by each icon.

All the social media links will be added to `index.html`.

### Why use it?

* ["Those tiny Tweet, Like, +1 buttons you see on websites are actually brutally large elements to load for (mobile) devices."](http://zurb.com/article/883/small-painful-buttons-why-social-media-bu)
* ["Services like AddThis and ShareThis will always spy on and tag your audience when you use their widgets"](http://ma.tt/2014/07/canvas-fingerprinting-addthis)

## TODO

* Find a better sprite image without gaps surrounding icons to reduce image size.
* Add all the social media links to `index.html`.
* Add 16x16 icons.
* Make a social media icon bar example for each icon size in `index.html`.

