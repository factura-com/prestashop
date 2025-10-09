class ModernProgressBar {
    constructor(elementId, options = {}) {
        this.element = document.getElementById(elementId);
        this.max = options.max || 100;
        this.value = options.value || 0;
        this.onComplete = options.complete || null;
        
        this.init();
    }
    
    init() {
        if (!this.element) {
            console.error('Progress bar element not found');
            return;
        }
        
        this.element.innerHTML = `
            <div class="progress-bar-fill"></div>
            <div class="progress-bar-text">0%</div>
        `;
        
        this.fillElement = this.element.querySelector('.progress-bar-fill');
        this.textElement = this.element.querySelector('.progress-bar-text');
        
        this.setCustomColor();
        
        this.updateDisplay();
    }
    
    setCustomColor() {
        const colorElement = document.querySelector('[style*="background-color"]');
        if (colorElement) {
            const style = window.getComputedStyle(colorElement);
            const color = style.backgroundColor;
            if (color && color !== 'rgba(0, 0, 0, 0)') {
                this.fillElement.style.background = color;
            }
        }
    }
    
    setValue(value) {
        this.value = Math.min(Math.max(value, 0), this.max);
        this.updateDisplay();
        
        if (this.value >= this.max && this.onComplete) {
            this.onComplete();
        }
    }
    
    updateDisplay() {
        const percentage = (this.value / this.max) * 100;
        this.fillElement.style.width = percentage + '%';
        this.textElement.textContent = Math.round(percentage) + '%';
    }
}

var valor = 0;
var int = 0;

document.addEventListener('DOMContentLoaded', function() {
    const progressElement = document.getElementById('progressbar');
    if (progressElement) {
        const progressBar = new ModernProgressBar('progressbar', {
            max: 100,
            value: valor,
            complete: function() {
                clearInterval(int);
            }
        });
        
        window.modernProgressBar = progressBar;
        
        function aumentar() {
            valor++;
            progressBar.setValue(valor);
        }
        
        int = setInterval(aumentar, 50);
    }
});

window.progress = function(percent, element) {
    const progressBar = window.modernProgressBar;
    if (progressBar) {
        progressBar.setValue(percent);
    } else if (element) {
        const fillElement = element.querySelector('.progress-bar-fill');
        const textElement = element.querySelector('.progress-bar-text');
        
        if (fillElement) {
            fillElement.style.width = percent + '%';
        }
        
        if (textElement) {
            textElement.textContent = Math.round(percent) + '%';
        }
    }
}; 