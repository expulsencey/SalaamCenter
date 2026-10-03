# Events

Source: user-supplied Training gestion administrative Salaam Center.pdf (35 pages) and the replacement WhatsApp video documented below.

Selected PDF pages: 2 (featured overview), 4 (discussion), 13 (trainer), 19 (note-taking), 24 (certificate presentation), 35 (final group).
Images extracted locally as JPEG, maximum width 1600 px, quality 90. Original compositions and printed branding preserved. No stock or generated imagery.
The supplied MP4 is copied once to assets/videos/administrative-management-training.mp4, without transcoding. The Downloads original is preserved.

Centralized data: data/events.php. Add a keyed event with a stable slug and the same supported media fields; Events lists the dataset, and the first entry is featured on the homepage. event.php validates the slug and uses one template. Only add verified facts/media.
Existing official articles remain in data/home.php and are shown separately under From the Archive on Events, not as current homepage activities.

Playback: a single video directly below the event introduction, followed by the editorial description and unchanged gallery. Muted autoplay, loop and playsinline; persistent observer pauses below 20% visibility and resumes when visible. Hidden tabs and reduced-motion preference pause playback. Play promise rejection is handled. Existing reveal system reused. No browser controls or play button.

No date, participant count, trainer identity, duration, organizations or sponsors added.

## Replacement video (3 October 2026)
User-supplied WhatsApp Video 2026-10-03 at 09.13.07.mp4 replaces the previous HEVC asset at the same public path. H.264/AVC (avc1), AAC (mp4a), 896 x 512 decoded in Edge (user described the export as 910 x 512); moov precedes mdat for Fast Start. File copied byte-for-byte with no transcoding. The original Downloads file is preserved. The source URL includes filemtime to invalidate the old cached HEVC file. Old HEVC-specific fallback and the later Training in Action video section removed.

QA: Edge headless decoded frames, readyState 4, currentTime advanced without interaction, loop tested by seeking near the end, viewport pause/resume and reduced-motion pause passed. Seven widths from 320 to 1440 px checked without overflow; one video and five gallery/completion images. No console errors. Other browsers not tested.

## Strategic & Commercial Development of Ports
Slug: strategic-commercial-development-ports. Second record in data/events.php; both cards now render on the homepage and Events using the shared component. The same detail template now uses event-specific video dimensions, labels, gallery heading/layout and an optional completion section.

Four supplied JPEGs copied without modification to assets/images/events/strategic-commercial-development-ports/:
- source 01: trainer-and-participants.jpg
- source 02: classroom-overview.jpg (card image and loading poster)
- source 03: presentation.jpg
- source 04: professional-exchange.jpg

Video copied without re-encoding to assets/videos/strategic-commercial-development-ports.mp4.
IMPORTANT: the actual supplied Downloads file has hvc1 (HEVC) and mp4a (AAC), encoded dimensions 1920 x 1080, moov after mdat. This contradicts the attachment's stated H.264 910 x 512 specifications. Edge loaded metadata but decoded zero video frames (videoWidth 0). The H.264 export path has been requested; actual moving playback remains unverified/unfinished for this event. A poster preserves meaningful content on browsers unable to decode this file; no HEVC warning is rendered publicly.

Eight viewport widths 320-1440 checked for both listings and the new detail page: no overflow, all four original photo compositions preserved. First event H.264 still decodes and plays. PHP lint and UTF-8 passed; no JavaScript exception observed. No dates, names, participant counts, client, certification or other unsupported details added.
