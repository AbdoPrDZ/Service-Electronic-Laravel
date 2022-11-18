import { Collection } from "./collection.js";

export class StorageListener {

  storageDatabase;
  listeners;

  constructor(storageDatabase) {
    this.storageDatabase = storageDatabase;
    this.listeners = {}
    setInterval(() => this.listnerLoop(), 200);
  }

  initListener(path, callback) {
    this.listeners[path] = {set: 1, get: 0, callback: callback};
  }

  setDate(path) {
    this.listeners[path].set = Date.now() + 1;
  }

  getDate(path) {
    this.listeners[path].get = Date.now();
  }

  getDates(path) {
    return this.listeners[path];
  }

  listnerPathExists(path) {
    return this.listeners[path] != undefined;
  }

  listnerLoop() {
    for (const path in this.listeners) {
      const listener = this.listeners[path];
      if (listener.set > listener.get) {
        listener.callback(path);
      }
    }
  }

}
