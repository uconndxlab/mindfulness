const DEFAULTS = {
    frameDurationMs: 450,
    holdDurationMs: 2500,
    loop: true,
};

export class FlowerAnimation {
    constructor(imgEl, config = {}) {
        this.imgEl = imgEl;
        this.config = { ...DEFAULTS, ...config };
        this.frames = Array.isArray(config.frames) ? config.frames : [];
        this.timeouts = [];
        this.stopped = false;
    }

    stop() {
        this.stopped = true;
        this.timeouts.forEach((timeoutId) => window.clearTimeout(timeoutId));
        this.timeouts = [];
    }

    wait(ms) {
        return new Promise((resolve) => {
            const timeoutId = window.setTimeout(resolve, ms);
            this.timeouts.push(timeoutId);
        });
    }

    setFrame(index) {
        const frame = this.frames[index];
        if (frame) {
            this.imgEl.src = frame;
        }
    }

    async playCycle() {
        if (!this.frames.length) {
            return;
        }

        for (let index = 0; index < this.frames.length; index++) {
            if (this.stopped) {
                return;
            }

            this.setFrame(index);

            if (index < this.frames.length - 1) {
                await this.wait(this.config.frameDurationMs);
            }
        }

        if (this.stopped) {
            return;
        }

        await this.wait(this.config.holdDurationMs);

        if (this.stopped) {
            return;
        }

        this.setFrame(0);
    }

    async play() {
        this.stopped = false;

        do {
            await this.playCycle();
        } while (this.config.loop && !this.stopped);
    }
}

export function playFlowerAnimation(imgEl, config) {
    const animation = new FlowerAnimation(imgEl, config);
    animation.play();

    return animation;
}
