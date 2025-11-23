'use client';

import { useState, useRef, useCallback, useEffect } from 'react';
import { X, ZoomIn, ZoomOut, RotateCcw } from 'lucide-react';
import { Dialog } from '@headlessui/react';

interface ProfileCropModalProps {
  isOpen: boolean;
  onClose: () => void;
  imageSrc: string;
  onSave: (croppedImage: string) => void;
}

export default function ProfileCropModal({ isOpen, onClose, imageSrc, onSave }: ProfileCropModalProps) {
  const [zoom, setZoom] = useState(100);
  const [position, setPosition] = useState({ x: 0, y: 0 });
  const [isDragging, setIsDragging] = useState(false);
  const [dragStart, setDragStart] = useState({ x: 0, y: 0 });
  const imageRef = useRef<HTMLImageElement>(null);
  const containerRef = useRef<HTMLDivElement>(null);

  const handleMouseDown = (e: React.MouseEvent) => {
    setIsDragging(true);
    setDragStart({
      x: e.clientX - position.x,
      y: e.clientY - position.y,
    });
  };

  const handleMouseMove = useCallback((e: MouseEvent) => {
    if (!isDragging || !containerRef.current) return;
    
    const container = containerRef.current;
    const rect = container.getBoundingClientRect();
    const maxX = (rect.width - 200) / 2;
    const maxY = (rect.height - 200) / 2;
    
    let newX = e.clientX - dragStart.x;
    let newY = e.clientY - dragStart.y;
    
    // Constrain to container bounds
    newX = Math.max(-maxX, Math.min(maxX, newX));
    newY = Math.max(-maxY, Math.min(maxY, newY));
    
    setPosition({ x: newX, y: newY });
  }, [isDragging, dragStart]);

  const handleMouseUp = useCallback(() => {
    setIsDragging(false);
  }, []);

  // Attach mouse event listeners
  useEffect(() => {
    if (isDragging) {
      document.addEventListener('mousemove', handleMouseMove);
      document.addEventListener('mouseup', handleMouseUp);
      return () => {
        document.removeEventListener('mousemove', handleMouseMove);
        document.removeEventListener('mouseup', handleMouseUp);
      };
    }
  }, [isDragging, handleMouseMove, handleMouseUp]);

  const handleZoomChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    setZoom(Number(e.target.value));
  };

  const handleResetPosition = () => {
    setPosition({ x: 0, y: 0 });
    setZoom(100);
  };

  const handleSave = () => {
    // Create a canvas to crop the image
    const canvas = document.createElement('canvas');
    const ctx = canvas.getContext('2d');
    if (!ctx || !imageRef.current || !containerRef.current) return;

    const img = imageRef.current;
    const size = 200; // Circular crop size
    canvas.width = size;
    canvas.height = size;

    // Wait for image to load
    if (!img.complete) {
      img.onload = () => handleSave();
      return;
    }

    const container = containerRef.current;
    const containerRect = container.getBoundingClientRect();
    const imgRect = imageRef.current.getBoundingClientRect();
    
    // Get actual displayed image dimensions
    const displayedWidth = imgRect.width;
    const displayedHeight = imgRect.height;
    
    // Calculate the crop circle center in container coordinates
    const cropCenterX = containerRect.width / 2;
    const cropCenterY = containerRect.height / 2;
    const cropRadius = size / 2;
    
    // Calculate the crop area in displayed image coordinates
    const cropLeftInDisplay = cropCenterX - cropRadius - (imgRect.left - containerRect.left);
    const cropTopInDisplay = cropCenterY - cropRadius - (imgRect.top - containerRect.top);
    
    // Convert to original image coordinates
    const scaleX = img.naturalWidth / displayedWidth;
    const scaleY = img.naturalHeight / displayedHeight;
    
    const sourceX = cropLeftInDisplay * scaleX;
    const sourceY = cropTopInDisplay * scaleY;
    const sourceSize = size * scaleX;
    
    // Draw circular mask
    ctx.beginPath();
    ctx.arc(size / 2, size / 2, size / 2, 0, Math.PI * 2);
    ctx.clip();
    
    // Draw image
    ctx.drawImage(
      img,
      Math.max(0, sourceX),
      Math.max(0, sourceY),
      Math.min(sourceSize, img.naturalWidth - Math.max(0, sourceX)),
      Math.min(sourceSize, img.naturalHeight - Math.max(0, sourceY)),
      0,
      0,
      size,
      size
    );

    // Convert to blob and create object URL
    canvas.toBlob((blob) => {
      if (blob) {
        const url = URL.createObjectURL(blob);
        onSave(url);
        onClose();
      }
    }, 'image/png');
  };

  return (
    <Dialog open={isOpen} onClose={onClose} className="relative z-50">
      {/* Dark Overlay */}
      <div className="fixed inset-0 bg-black/60" aria-hidden="true" onClick={onClose} />

      {/* Modal */}
      <div className="fixed inset-0 flex items-center justify-center p-4">
        <Dialog.Panel className="bg-white rounded-xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-hidden flex flex-col">
          {/* Header */}
          <div className="flex items-center justify-between p-6 border-b">
            <div>
              <Dialog.Title className="text-2xl font-header font-bold text-gray-900">
                Crop Profile Photo
              </Dialog.Title>
              <p className="text-sm text-gray-600 mt-1">
                Drag the image to position it, then adjust the zoom to fit
              </p>
            </div>
            <button
              onClick={onClose}
              className="text-gray-400 hover:text-gray-600 transition-colors"
            >
              <X className="w-6 h-6" />
            </button>
          </div>

          {/* Image Cropping Area */}
          <div className="flex-1 p-6 flex items-center justify-center bg-gray-50">
            <div
              ref={containerRef}
              className="relative w-full max-w-md h-96 bg-gray-100 rounded-lg overflow-hidden"
              style={{ cursor: isDragging ? 'grabbing' : 'grab' }}
            >
              {/* Image */}
              <img
                ref={imageRef}
                src={imageSrc}
                alt="Profile crop"
                className="absolute select-none z-0"
                style={{
                  width: 'auto',
                  height: 'auto',
                  maxWidth: `${zoom}%`,
                  maxHeight: `${zoom}%`,
                  objectFit: 'contain',
                  left: '50%',
                  top: '50%',
                  transform: `translate(calc(-50% + ${position.x}px), calc(-50% + ${position.y}px))`,
                  transition: isDragging ? 'none' : 'transform 0.1s ease-out',
                }}
                onMouseDown={handleMouseDown}
                draggable={false}
              />

              {/* Circular Crop Overlay - Above image */}
              <div className="absolute inset-0 flex items-center justify-center pointer-events-none z-10">
                <div className="w-[200px] h-[200px] rounded-full border-4 border-white shadow-lg" />
              </div>
            </div>
          </div>

          {/* Controls */}
          <div className="p-6 border-t bg-gray-50">
            <div className="flex items-center gap-6 mb-6">
              {/* Preview */}
              <div className="flex items-center gap-3">
                <div className="w-16 h-16 rounded-full bg-gray-200 overflow-hidden border-2 border-gray-300 flex items-center justify-center">
                  <img
                    src={imageSrc}
                    alt="Preview"
                    className="w-full h-full object-cover"
                    style={{
                      transform: `scale(${zoom / 100}) translate(${position.x / 10}px, ${position.y / 10}px)`,
                    }}
                  />
                </div>
                <span className="text-sm text-gray-600 font-medium">Preview</span>
              </div>

              {/* Zoom Slider */}
              <div className="flex-1 flex items-center gap-3">
                <ZoomOut className="w-5 h-5 text-gray-400" />
                <input
                  type="range"
                  min="50"
                  max="200"
                  value={zoom}
                  onChange={handleZoomChange}
                  className="flex-1 h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-black"
                />
                <ZoomIn className="w-5 h-5 text-gray-400" />
                <span className="text-sm text-gray-600 font-medium w-12 text-right">{zoom}%</span>
              </div>

              {/* Reset Position Button */}
              <button
                onClick={handleResetPosition}
                className="flex items-center gap-2 px-4 py-2 text-gray-700 bg-white border-2 border-gray-300 rounded-lg hover:bg-gray-50 transition-colors font-medium text-sm"
              >
                <RotateCcw className="w-4 h-4" />
                Reset Position
              </button>
            </div>

            {/* Action Buttons */}
            <div className="flex items-center justify-end gap-3">
              <button
                onClick={onClose}
                className="px-6 py-2.5 bg-white border-2 border-gray-300 text-gray-700 rounded-lg font-semibold hover:bg-gray-50 transition-colors"
              >
                Cancel
              </button>
              <button
                onClick={handleSave}
                className="px-6 py-2.5 bg-black text-white rounded-lg font-semibold hover:bg-gray-800 transition-colors"
              >
                Save Photo
              </button>
            </div>
          </div>
        </Dialog.Panel>
      </div>
    </Dialog>
  );
}

