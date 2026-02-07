(() => {
  const sent = new WeakSet();
  const defaultThreshold = 0.85;

  const resolveThreshold = (el) => {
    const raw = el?.dataset?.threshold;
    if (!raw) {
      return defaultThreshold;
    }
    const value = parseFloat(raw);
    if (Number.isFinite(value) && value > 0 && value <= 1) {
      return value;
    }
    return defaultThreshold;
  };

  const showToast = (message) => {
    const toast = document.querySelector(".elearning-toast");
    if (!toast || !message) {
      return;
    }
    toast.textContent = message;
    toast.classList.add("is-visible");
    window.clearTimeout(toast._hideTimer);
    toast._hideTimer = window.setTimeout(() => {
      toast.classList.remove("is-visible");
    }, 2600);
  };

  const updateCompletionUi = () => {
    const container = document.querySelector(".elearning-lesson-completion");
    if (!container) {
      return;
    }
    container.dataset.completed = "1";
    const label = container.querySelector(".elearning-completed-label");
    const buttonWrap = container.querySelector(".elearning-mark-complete");
    if (label) {
      label.classList.remove("is-hidden");
    }
    if (buttonWrap) {
      buttonWrap.classList.add("is-hidden");
    }
    const message = container.dataset.toastMessage;
    showToast(message);
  };

  const sendComplete = (el) => {
    if (sent.has(el)) {
      return;
    }
    const url = el.dataset.completeUrl;
    if (!url) {
      return;
    }
    sent.add(el);
    fetch(url, { credentials: "same-origin", headers: { Accept: "application/json" } })
      .then((response) => {
        if (response.ok) {
          updateCompletionUi();
        }
        return response
          .json()
          .catch(() => null)
          .then((data) => ({ response, data }));
      })
      .then(({ response, data }) => {
        if (!response.ok) {
          throw new Error("Request failed");
        }
        if (data && data.ok === false) {
          throw new Error("Not completed");
        }
      })
      .catch(() => {
        sent.delete(el);
      });
  };

  const initHtml5 = (video) => {
    const threshold = resolveThreshold(video);
    const onTime = () => {
      if (!video.duration || Number.isNaN(video.duration)) {
        return;
      }
      if (video.currentTime / video.duration >= threshold) {
        sendComplete(video);
        video.removeEventListener("timeupdate", onTime);
      }
    };
    video.addEventListener("timeupdate", onTime);
  };

  const initYouTube = (iframe) => {
    const threshold = resolveThreshold(iframe);
    if (!window.YT || !window.YT.Player) {
      return;
    }
    const player = new window.YT.Player(iframe, {
      events: {
        onStateChange: (event) => {
          if (event.data !== window.YT.PlayerState.PLAYING) {
            return;
          }
          const tick = () => {
            const duration = player.getDuration();
            if (duration > 0 && player.getCurrentTime() / duration >= threshold) {
              sendComplete(iframe);
            } else {
              window.requestAnimationFrame(tick);
            }
          };
          tick();
        },
      },
    });
    iframe._elearningPlayer = player;
  };

  const initVimeo = (iframe) => {
    const threshold = resolveThreshold(iframe);
    if (!window.Vimeo || !window.Vimeo.Player) {
      return;
    }
    const player = new window.Vimeo.Player(iframe);
    player.on("timeupdate", (data) => {
      if (data && data.duration > 0 && data.percent >= threshold) {
        sendComplete(iframe);
        player.off("timeupdate");
      }
    });
    iframe._elearningPlayer = player;
  };

  const loadScript = (src) =>
    new Promise((resolve) => {
      const script = document.createElement("script");
      script.src = src;
      script.async = true;
      script.onload = resolve;
      document.head.appendChild(script);
    });

  const init = async () => {
    const videos = Array.from(document.querySelectorAll(".elearning-video"));
    if (videos.length === 0) {
      return;
    }

    const youtubeVideos = videos.filter((el) => el.dataset.provider === "youtube");
    const vimeoVideos = videos.filter((el) => el.dataset.provider === "vimeo");
    const html5Videos = videos.filter((el) => el.dataset.provider === "html5");

    html5Videos.forEach(initHtml5);

    if (youtubeVideos.length > 0) {
      if (!window.YT || !window.YT.Player) {
        await loadScript("https://www.youtube.com/iframe_api");
        await new Promise((resolve) => {
          const prev = window.onYouTubeIframeAPIReady;
          window.onYouTubeIframeAPIReady = () => {
            if (typeof prev === "function") {
              prev();
            }
            resolve();
          };
        });
      }
      youtubeVideos.forEach(initYouTube);
    }

    if (vimeoVideos.length > 0) {
      if (!window.Vimeo || !window.Vimeo.Player) {
        await loadScript("https://player.vimeo.com/api/player.js");
      }
      vimeoVideos.forEach(initVimeo);
    }
  };

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init);
  } else {
    init();
  }
})();
