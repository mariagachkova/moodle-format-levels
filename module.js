// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * format_levels_renderer
 *
 * @package    format_levels
 */

M.format_levels = M.format_levels || {
    ourYUI: null,
    numsections: 0
};

M.format_levels.init = function(Y, numsections) {
    this.ourYUI = Y;
    this.numsections = parseInt(numsections);
    document.getElementById('levelsectioncontainer').style.display = 'table';
};

M.format_levels.hide = function() {
    for (var i = 1; i <= this.numsections; i++) {
        if (document.getElementById('levelsection-' + i) != undefined) {
            var levelsection = document.getElementById('levelsection-' + i);
            levelsection.setAttribute('class', levelsection.getAttribute('class').replace('activesection', ''));
            document.getElementById('section-' + i).style.display = 'none';
        }
    }
};

M.format_levels.show = function(id, courseid) {
    this.hide();
    var levelsection = document.getElementById('levelsection-' + id);
    var currentsection = document.getElementById('section-' + id);
    levelsection.setAttribute('class', levelsection.getAttribute('class') + ' activesection');
    currentsection.style.display = 'block';
    document.cookie = 'activesection_' + courseid + '=' + id + '; path=/';
    M.format_levels.h5p();
};

M.format_levels.h5p = function() {
    window.h5pResizerInitialized = false;
    var iframes = document.getElementsByTagName('iframe');
    var ready = {
        context: 'h5p',
        action: 'ready'
    };
    for (var i = 0; i < iframes.length; i++) {
        if (iframes[i].src.indexOf('h5p') !== -1) {
            iframes[i].contentWindow.postMessage(ready, '*');
        }
    }
};