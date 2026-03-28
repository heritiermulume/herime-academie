import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

// Import Plyr for custom video player
import Plyr from 'plyr';
import 'plyr/dist/plyr.css';
import { adjustVideoPreloadForConnection } from './video-preload';
import { attachHlsToVideo } from './video-hls';

window.Plyr = Plyr;
window.adjustVideoPreloadForConnection = adjustVideoPreloadForConnection;
window.herimeAttachHlsToVideo = attachHlsToVideo;
