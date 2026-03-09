AOS.init({
    duration: 800,
    once: true,
    easing: "ease-out-cubic"
});


function parallaxHero() {

    return {

        x: 0,
        y: 0,

        move(e) {

            let rect = e.currentTarget.getBoundingClientRect()

            this.x = (rect.width / 2 - e.clientX) / 40
            this.y = (rect.height / 2 - e.clientY) / 40

        },

        style(speed) {

            return `transform:translate(${this.x*speed}px,${this.y*speed}px);transition:transform 0.1s;`

        }

    }

}


function counter(target) {

    return {

        count: 0,

        init() {

            let step = Math.ceil(target / 60)

            let interval = setInterval(() => {

                if (this.count >= target) {

                    this.count = target
                    clearInterval(interval)

                } else {

                    this.count += step

                }

            }, 16)

        }

    }

}