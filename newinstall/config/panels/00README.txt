DEPRECATED AND GET REMOVED IN THE NEAR FUTURE!!!



Panels
======

This directory holds Admin, Donate, Records and Vote panel templates,
along with PanelBG background templates, managed by plugin.panels.php.
Panel templates define the complete ManiaLink panel with position,
size and fonts, so you have full control to develop custom panels.
Background templates define only the (sub)style of the background
of the admin, donate, records and vote panels, as well as the player
stats panel at the scoreboard.  The server's default background is
used for the CPS (checkpoints) panel and the system message window.

To create a new template, copy an existing one to a new filename and
edit that, as the existing set will be overwritten in future releases.

Use ManiaLink http://smurf1.free.fr/mle2/index.php
and XML list  http://smurf1.free.fr/mle2/list.php
to select styles and fonts.  Note that not every (sub)style and font
fits everywhere due to size variations.

New Admin templates must stick to manialink id="3" and preserve
action="21" through action="27" for the buttons.

New Donate templates must stick to manialink id="6" and preserve
action="30" through action="36" for the buttons.

New Record templates must stick to manialink id="4" and preserve the
mapping of text="%PB%" to action="7", "%LCL%" to "8", "%DED%" to "9"
and "%MX%" to "10".

New Vote templates must stick to manialink id="5" and preserve
action="18" for the Yes button and action="19" for the No button.

To let the panels use the customizable backgrounds, the pertaining
quads must use the style="%STYLE%" substyle="%SUBST%" placeholders.

If you create a nice template for any panel or background that's
sufficiently distinct from the standard ones, send it to me and I
might include it in the next UASECO release. :)
