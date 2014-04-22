The MattyG Web Framework

This is a general-purpose web framework that is designed from the ground up to
be simple and minimalist. It makes a few assumptions, but leaves most of the
work up to you. It requires at least PHP 5.5.

This framework is designed to be heavily driven by JSON configuration files.
Everything from database connection details to basic site information is
designed to be stored in JSON files and read through a central configuration
object. It also utilises a configuration-based layout system to help determine
what content is rendered on each page.

Everything is designed to be overridden by the user of the framework. This is
definitely true of the code and the configuration. Large parts of the
framework will be usable without writing any code, purely because the
configuration is completely exstensible.

This framework is a work in progress. It is still missing a lot of things, and
as such is currently just at v0.7.1. The following is a list of things that are
still missing or still need work:
- A base design. I think it's pretty much where I want it now, but I wouldn't
  be surprised if I find a few little necessary touch-ups.
- Documentation. I have not had time to write any yet. The first focus will be
  to ensure all functions in all core classes have proper PHPDoc. I've made
  some progress on this, but still have a way to go.
- The model layer still needs a bit of work. The DatabaseSave trait has not
  been tested in any way.

Notes:
- I've made the decision to only make translation available within views. I
  realised that this isn't exactly functionality I need for myself anyway, so
  I don't think it's that big of a deal.


Contributions

I welcome any and all contributions to this project, but the decision as to
which contributions are accepted will ultimately be mine. While I am happy for
anyone to use this project for whatever purpose they see fit, the reason I
created this framework was to power other personal projects.


License

The entirety of this code base is released into the public domain. You are free
to do whatever you want with the code and use it for whatever purpose you see
fit, without attribution. Please see LICENSE.txt for more details.
Dependencies each have their own individual licenses. Please see each
repository for information about its license.
