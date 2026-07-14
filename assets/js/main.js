/*
 * Frontend entry point for the RateIt bundle.
 *
 * Bundles the rating widget together with its helper libraries and both CSS
 * skins (star + heart). Only the skin matching the configured rating type is
 * actually used at runtime, because the skins are scoped to the
 * `.rateit-stars` / `.rateit-hearts` modifier class set by the templates.
 *
 * The widget itself works on top of either MooTools or jQuery, whichever the
 * host page provides (see rateit.js).
 */
import './helper.js';
import './jquery-ui-effects.custom.js';
import './onReadyRateIt.js';
import './rateit.js';

import '../css/rateit.css';
import '../css/star.css';
import '../css/heart.css';
