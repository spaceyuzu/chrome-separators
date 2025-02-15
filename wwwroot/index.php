<?php
$VERSION = '1.4';

header('X-Content-Type-Options: nosniff');

// NOTE: Even though "t=horz" is no longer used, there are users out there that created
//   bookmarks when this was still in use. And Chrome periodically rescans the favicons
//   for bookmarks. Then, if we'd drop this check for "t=horz", these users would suddenly
//   see the vertical favicon for their horizontal separators. See #20.
$isHorizontal = isset($_GET['horz']) || @$_GET['t'] == 'horz';

if (!$isHorizontal && !isset($_GET['vert'])) {
  // If neither "?horz" nor "?vert", select "?vert" explicitly - or else the icon on the user's bookmark bar
  // will have a generic icon - and not our intended favicon.
  //
  // NOTE: We use a 301 (permanent redirect) here - instead of 302 (temporary redirect) - to indicate to
  //   browsers that they should update their bookmarks (if they support such a feature).
  header("Location: /?vert", true, 301);
  exit();
}
?>
<!doctype html>
<html lang="en">
<head>
  <title>Chrome Bookmarks Separator</title>

  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />

  <!-- See: https://css-tricks.com/svg-favicons-and-all-the-fun-things-we-can-do-with-them/ -->
  <link rel="icon" href="favicon-<?php echo $isHorizontal ? 'horz' : 'vert'; ?>.svg" type="image/svg+xml" />

  <link rel="stylesheet" href="index.css" />
</head>

<body>
  <div id="outer">
    <div id="inner">

      <p>
        I'm a <b><?php echo $isHorizontal ? '— horizontal' : '| vertical'; ?></b> separator.
        Drag <a class="me" href="<?php echo $isHorizontal ? "/?horz" : "/?vert"; ?>" title="Drag me!"><?php echo $isHorizontal ? '───────────' : ''; ?></a><br/>
        to your bookmarks <span class="target-name"><?php echo $isHorizontal ? 'folder' : 'toolbar'; ?></span>.
      </p>

      <p>
        For a <b><?php echo $isHorizontal ? '| vertical' : '— horizontal'; ?></b> separator, click <a href="<?php echo $isHorizontal ? '/?vert' : '/?horz'; ?>">here</a>.
      </p>

      <p>
        <b>:)</b>
      </p>

      <div class="footer">
        <p>
          Version <?php echo $VERSION; ?><br/>
          Made by <a href="https://hachyderm.io/@manski" target="_blank">@manski</a><br/>
          (on <a href="https://github.com/skrysmanski/chrome-separators" target="_blank">GitHub</a>)
        </p>
      </div>
    </div>
  </div>
</body>

<script>
  document.addEventListener('DOMContentLoaded', (event) => {
    if(typeof crypto.randomUUID === 'undefined') {
      return;
    }

    const urlParams = new URLSearchParams(window.location.search);
    const mode = (urlParams.get('horz') !== null) ? 'horz' : 'vert';
    const link = document.querySelector(".me");

    // This method exists so that this site can be used for bookmarks in Edge. Edge doesn't allow the user
    // to place multiple bookmarks to the same URL into the same bookmarks folder (unlike Chrome which allows
    // this). So, to work around this problem, this method changes the bookmark URL so that no two separator
    // bookmarks have the same URL.
    function refreshHash() {
      const randomKey = crypto.randomUUID();

      // To reduce confusion about the random key, we add "bookmark-differentiator" in front of it.
      // This way, users can more easily guess that this key is only used to differentiate between bookmarks.
      const differentiatorKey = `bookmark-differentiator--${randomKey}`;

      link.setAttribute('href', `?${mode}#${differentiatorKey}`);

      // NOTE: We use "location.replace()" here - instead of "location.hash" - so that
      //   the browser doesn't create a new browser history item for each new key.
      // NOTE 2: We need to change the current page's location or else the browser won't pre-cache
      //   the page's favicon. I.e. without this, if the user drags the "me" button to their
      //   bookmarks bar, the bookmark's icon will be a generic icon - and not the actual favicon
      //   of this page (until the user clicks on the bookmark).
      window.location.replace(`#${differentiatorKey}`);
    }

    refreshHash();

    link.addEventListener("mousedown", (event) => {
      if (event.button === 0) {
        refreshHash();
      }
    });
  });
</script>

</html>
