The MattyG Web Framework

This is a general-purpose web framework that is designed from the ground up to
be simple and minimalist. It makes a few assumptions, but leaves most of the
work up to you. It requires at least PHP 5.5.

This framework is designed to be heavily driven by JSON configuration files.
Everything from database connection details to basic site information is
designed to be stored in JSON files and read through a central configuration
object. It also utilises a JSON-based layout system to help determine what
content is rendered on each page.

Everything is designed to be overridden by the user of the framework. This is
definitely true of the code, and is almost completely true of the configuration
system. Unfortunately, this is not perfect yet, and still requires some work.

This framework is a work in progress. It is still missing a lot of things, and
as such is currently just at v0.3.0. The following is a list of things that are
still missing or still need work:
- A base design. It is still incredibly basic. The top priorities are a design
  for the 404 page, and getting the top nav bar working correctly.
- Documentation. I have not had time to write any yet. The first focus will be
  to ensure all functions in all core classes have proper PHPDoc.
- The model layer has not yet been implemented in any way, shape or form, and
  there is no way to perform any sort of database access in user-based code at
  present.
- URL generation. The plan is to hook into the Aura.Router module's URL
  generation features.
- Cache. Currently there is no way to programmatically evict entries from the
  cache, and expiry times for cache entries are not actually used.
- Translation files and routes need to be cached.

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
