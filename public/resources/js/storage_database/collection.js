import { Doc } from "./doc.js";

export class Collection {

  collectionId;
  storageDatabase;
  storageListener;

  constructor(collectionId, storageDatabase, storageListener) {
    this.collectionId = collectionId;
    this.storageDatabase = storageDatabase;
    this.storageListener = storageListener;
    // if (!this.storageDatabase.storage[this.collectionId]) {
    //   this.storageDatabase.storage[this.collectionId] = {};
    // }
  }

  get(log = true) {
    if (log && this.storageListener.listnerPathExists(this.getPath())) this.storageListener.getDate(this.getPath());
    return this.storageDatabase.storage[this.collectionId];
  }

  set(value, replace = false, log = true) {
    var collectionData = this.get(false);
    if (replace || collectionData == undefined) {
      collectionData = value;
    } else {
      if (Array.isArray(collectionData) && Array.isArray(value)) {
        collectionData = collectionData.concat(value);
      } else if (typeof collectionData == 'object' && typeof value == 'object') {
        collectionData = Object.assign(collectionData, value);
      } else if (typeof collectionData == typeof value) {
        collectionData = value;
      } else {
        throw new Error('Invalid value type');
      }
    }
    if (log && this.storageListener.listnerPathExists(this.getPath())) this.storageListener.setDate(this.getPath());
    this.storageDatabase.storage[this.collectionId] = collectionData

  }

  haveDocId(docId) {
    var collectionData = this.get(false);
    if (collectionData && !Array.isArray(collectionData) && typeof collectionData == 'object') {
      return collectionData[docId] != undefined;
    } else {
      throw new Error('This collection not support sub docs');
    }
  }

  doc(docId) {
    var collectionData = this.get(false);
    if (collectionData == undefined) {
      this.set({});
      collectionData = this.get(false);
    }
    if (!Array.isArray(collectionData) && typeof collectionData == 'object') {
      return new Doc(docId, this, this.storageListener);
    } else {
      throw new Error('This collection not support sub docs');
    }
  }

  delete() {
    this.storageDatabase.remove(this.collectionId);
  }

  remove(docId) {
    var collectionData = this.get(false);
    if (collectionData && !Array.isArray(collectionData) && typeof collectionData == 'object') {
      delete collectionData[docId];
      this.set(collectionData, true);
    } else {
      throw new Error('This collection not support sub docs');
    }
  }

  getPath() {
    return this.collectionId;
  }

  listen(callback) {
    this.storageListener.initListener(this.getPath(), () => {
      callback(this.get());
    });
  }

}
