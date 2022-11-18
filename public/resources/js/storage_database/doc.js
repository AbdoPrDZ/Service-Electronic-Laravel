export class Doc {

  docId;
  parrent;
  storageListener;

  constructor(docId, parrent, storageListener) {
    this.docId = docId;
    this.parrent = parrent;
    this.storageListener = storageListener;
    // if (!this.parrent.get()[this.docId]) {
    //   this.parrent.set({[this.docId]: {}}, true);
    // }
  }

  get(log = true) {
    if (log && this.storageListener.listnerPathExists(this.getPath())) this.storageListener.getDate(this.getPath());
    return this.parrent.get()[this.docId];
  }

  set(value, replace = false, log = true) {
    var docData = this.get();
    if (replace || docData == undefined) {
      docData = value;
    } else {
      if (Array.isArray(docData) && Array.isArray(value)) {
        docData = docData.concat(value);
      } else if (typeof docData == 'object' && typeof value == 'object') {
        docData = Object.assign(docData, value);
      } else if (typeof docData == typeof value) {
        docData = value;
      } else {
        throw new Error('Invalid value type');
      }
    }
    if (log && this.storageListener.listnerPathExists(this.getPath())) this.storageListener.setDate(this.getPath());
    this.parrent.set({[this.docId]: docData});
  }

  haveDocId(docId) {
    var docData = this.get(false);
    if (docData && !Array.isArray(docData) && typeof docData == 'object') {
      return docData[docId] != undefined;
    } else {
      throw new Error('This doc not support sub docs');
    }
  }

  doc(docId) {
    var docData = this.get();
    if (docData && !Array.isArray(docData) && typeof docData == 'object') {
      return new Doc(docId, this, this.storageListener);
    } else {
      throw new Error('This doc not support sub docs');
    }
  }

  delete() {
    this.parrent.remove(this.docId);
  }

  remove(docId) {
    var docData = this.get(false);
    if (docData && !Array.isArray(docData) && typeof docData == 'object') {
      delete docData[docId];
      this.set(docData, true);
    } else {
      throw new Error('This doc not support sub docs');
    }
  }

  getPath() {
    return `${this.parrent.getPath()}/${this.docId}`;
  }
  
  listen(callback) {
    this.storageListener.initListener(this.getPath(), () => callback(this.get()));
  }

}