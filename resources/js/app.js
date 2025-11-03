import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

// Import Plyr for custom video player
import Plyr from 'plyr';
import 'plyr/dist/plyr.css';
window.Plyr = Plyr;
