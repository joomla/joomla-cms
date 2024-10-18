/**
 * Simple timer
 *
 * @param name
 * @returns {{stop: stop}}
 */
export class Timer {
  constructor(name) {
    this.start = new Date();
    this.name = name;
  }

  stop() {
    const end = new Date();
    const time = end.getTime() - this.start.getTime();
    // eslint-disable-next-line no-console
    console.log('Timer:', this.name, 'finished in', time, 'ms');
  }
}
