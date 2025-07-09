// Firebase utility functions for additional operations
import {
  ref,
  uploadBytesResumable,
  getDownloadURL,
  addDoc,
  collection,
  query,
  where,
  orderBy,
  getDocs,
  deleteDoc,
  deleteObject,
  updateDoc,
  doc,
} from "firebase/firestore"

class FirebaseManager {
  constructor(app, db, storage) {
    this.app = app
    this.db = db
    this.storage = storage
  }

  // Batch upload multiple files
  async batchUpload(files, category, uploadedBy) {
    const results = []
    const totalFiles = files.length

    for (let i = 0; i < totalFiles; i++) {
      const file = files[i]
      try {
        const result = await this.uploadSingleFile(file, category, uploadedBy, i + 1, totalFiles)
        results.push(result)
      } catch (error) {
        console.error(`Failed to upload file ${i + 1}:`, error)
        results.push({ error: error.message, fileName: file.name })
      }
    }

    return results
  }

  // Upload single file with progress tracking
  async uploadSingleFile(file, category, uploadedBy, currentIndex = 1, totalFiles = 1) {
    return new Promise((resolve, reject) => {
      const fileName = `${Date.now()}_${currentIndex}_${file.name}`
      const storagePath = `media/${category}/${fileName}`
      const storageRef = ref(this.storage, storagePath)

      const uploadTask = uploadBytesResumable(storageRef, file)

      uploadTask.on(
        "state_changed",
        (snapshot) => {
          const progress = (snapshot.bytesTransferred / snapshot.totalBytes) * 100
          this.updateBatchProgress(currentIndex, totalFiles, progress)
        },
        (error) => {
          reject(error)
        },
        async () => {
          try {
            const downloadURL = await getDownloadURL(uploadTask.snapshot.ref)

            const docData = {
              name: file.name.split(".")[0],
              category: category,
              fileName: fileName,
              originalName: file.name,
              fileType: file.type,
              fileSize: file.size,
              downloadURL: downloadURL,
              storagePath: storagePath,
              uploadedBy: uploadedBy,
              uploadedAt: new Date().toISOString(),
              status: "active",
            }

            // Use category as collection name
            const docRef = await addDoc(collection(this.db, category), docData)

            resolve({
              id: docRef.id,
              collection: category,
              ...docData,
            })
          } catch (error) {
            reject(error)
          }
        },
      )
    })
  }

  // Update batch upload progress
  updateBatchProgress(currentIndex, totalFiles, fileProgress) {
    const overallProgress = ((currentIndex - 1) / totalFiles) * 100 + fileProgress / totalFiles
    console.log(`Batch Progress: ${overallProgress.toFixed(1)}% (File ${currentIndex}/${totalFiles})`)
  }

  // Get media by category
  async getMediaByCategory(category) {
    try {
      // Query the category collection directly
      const q = query(collection(this.db, category), where("status", "==", "active"), orderBy("uploadedAt", "desc"))

      const querySnapshot = await getDocs(q)
      const media = []

      querySnapshot.forEach((doc) => {
        media.push({
          id: doc.id,
          collection: category,
          ...doc.data(),
        })
      })

      return media
    } catch (error) {
      console.error(`Error getting media from ${category} collection:`, error)
      throw error
    }
  }

  // Delete media item
  async deleteMedia(mediaId, storagePath, category) {
    try {
      // Delete from the specific category collection
      await deleteDoc(doc(this.db, category, mediaId))

      // Delete from Storage
      if (storagePath) {
        const storageRef = ref(this.storage, storagePath)
        await deleteObject(storageRef)
      }

      return { success: true, message: `Media deleted from ${category} collection successfully` }
    } catch (error) {
      console.error("Error deleting media:", error)
      throw error
    }
  }

  // Update media metadata
  async updateMedia(mediaId, updateData, category) {
    try {
      const mediaRef = doc(this.db, category, mediaId)
      await updateDoc(mediaRef, {
        ...updateData,
        updatedAt: new Date().toISOString(),
      })

      return { success: true, message: `Media in ${category} collection updated successfully` }
    } catch (error) {
      console.error("Error updating media:", error)
      throw error
    }
  }

  // Get analytics data
  async getAnalytics() {
    try {
      const categories = ["portraits", "family", "headshots", "videos"]
      let totalMedia = 0
      const mediaByCategory = {}
      const recentUploads = []

      // Get data from each category collection
      for (const category of categories) {
        try {
          const categorySnapshot = await getDocs(collection(this.db, category))
          const categoryCount = categorySnapshot.size

          mediaByCategory[category] = categoryCount
          totalMedia += categoryCount

          // Get recent uploads from this category
          categorySnapshot.forEach((doc) => {
            const data = doc.data()
            const uploadDate = new Date(data.uploadedAt)
            const weekAgo = new Date()
            weekAgo.setDate(weekAgo.getDate() - 7)

            if (uploadDate > weekAgo) {
              recentUploads.push({
                id: doc.id,
                collection: category,
                ...data,
              })
            }
          })
        } catch (error) {
          console.log(`${category} collection not found or empty`)
          mediaByCategory[category] = 0
        }
      }

      // Get users count (assuming users collection exists)
      let totalUsers = 0
      try {
        const usersSnapshot = await getDocs(collection(this.db, "users"))
        totalUsers = usersSnapshot.size
      } catch (error) {
        console.log("Users collection not found")
      }

      const analytics = {
        totalMedia,
        totalUsers,
        mediaByCategory,
        recentUploads: recentUploads.sort((a, b) => new Date(b.uploadedAt) - new Date(a.uploadedAt)),
      }

      return analytics
    } catch (error) {
      console.error("Error getting analytics:", error)
      throw error
    }
  }
}

// Export for use in other scripts
window.FirebaseManager = FirebaseManager
