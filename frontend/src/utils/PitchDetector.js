class PitchDetector {
  constructor(onPitchDetected) {
    this.onPitchDetected = onPitchDetected;
    this.audioContext = null;
    this.analyser = null;
    this.microphone = null;
    this.bufferLength = 0;
    this.dataArray = null;
    this.rafId = null;
  }

  async start() {
    try {
      // Get microphone access
      const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
      
      // Create audio context
      this.audioContext = new (window.AudioContext || window.webkitAudioContext)();
      this.analyser = this.audioContext.createAnalyser();
      this.microphone = this.audioContext.createMediaStreamSource(stream);
      
      // Configure analyser
      this.analyser.fftSize = 2048;
      this.bufferLength = this.analyser.frequencyBinCount;
      this.dataArray = new Uint8Array(this.bufferLength);
      
      // Connect microphone to analyser
      this.microphone.connect(this.analyser);
      
      // Start detection loop
      this.detectPitch();
    } catch (error) {
      console.error('Error accessing microphone:', error);
      throw error;
    }
  }

  detectPitch() {
    this.rafId = requestAnimationFrame(() => this.detectPitch());
    
    // Get frequency data
    this.analyser.getByteFrequencyData(this.dataArray);
    
    // Find peak frequency
    let maxIndex = 0;
    let maxValue = 0;
    
    for (let i = 0; i < this.bufferLength; i++) {
      if (this.dataArray[i] > maxValue) {
        maxValue = this.dataArray[i];
        maxIndex = i;
      }
    }
    
    // Convert to frequency (Hz)
    const nyquist = this.audioContext.sampleRate / 2;
    const frequency = (maxIndex / this.bufferLength) * nyquist;
    
    // Only report if there's significant volume
    if (maxValue > 50) {
      this.onPitchDetected(frequency);
    }
  }

  stop() {
    if (this.rafId) {
      cancelAnimationFrame(this.rafId);
    }
    
    if (this.microphone) {
      this.microphone.disconnect();
    }
    
    if (this.audioContext) {
      this.audioContext.close();
    }
  }
}

export default PitchDetector;
