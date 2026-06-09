import { useState, useMemo } from 'react';

interface Props {
  defaultImage: string;
  allImages: string[];
  colorGalleries: Record<string, string[]>;
  colors: string[];
  selectedColor: string | null;
  onColorSelect: (color: string | null) => void;
}

export default function ProductGallery({
  defaultImage,
  allImages,
  colorGalleries,
  colors,
  selectedColor,
  onColorSelect,
}: Props) {
  const [activeIndex, setActiveIndex] = useState(0);

  const displayImages = useMemo(() => {
    if (selectedColor && colorGalleries[selectedColor]?.length) {
      return colorGalleries[selectedColor];
    }
    return allImages.length > 0 ? allImages : [defaultImage];
  }, [selectedColor, colorGalleries, allImages, defaultImage]);

  const activeImage = displayImages[activeIndex] || defaultImage;

  return (
    <div className="flex flex-col gap-4">
      {/* Main image */}
      <div className="aspect-[3/4] bg-gray-100 rounded-2xl overflow-hidden relative">
        <img
          src={activeImage}
          alt="Product"
          className="w-full h-full object-cover transition-opacity duration-300"
        />
        {displayImages.length > 1 && (
          <>
            <button
              onClick={() => setActiveIndex((i) => Math.max(0, i - 1))}
              disabled={activeIndex === 0}
              className="absolute left-3 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full bg-white/80 backdrop-blur-sm shadow flex items-center justify-center disabled:opacity-30 hover:bg-white transition"
            >
              ‹
            </button>
            <button
              onClick={() => setActiveIndex((i) => Math.min(displayImages.length - 1, i + 1))}
              disabled={activeIndex === displayImages.length - 1}
              className="absolute right-3 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full bg-white/80 backdrop-blur-sm shadow flex items-center justify-center disabled:opacity-30 hover:bg-white transition"
            >
              ›
            </button>
          </>
        )}
      </div>

      {/* Thumbnails row */}
      <div className="flex gap-2 overflow-x-auto pb-1">
        {displayImages.map((img, i) => (
          <button
            key={i}
            onClick={() => setActiveIndex(i)}
            className={`flex-shrink-0 w-16 h-20 rounded-lg overflow-hidden border-2 transition ${
              i === activeIndex
                ? 'border-black dark:border-white'
                : 'border-transparent opacity-60 hover:opacity-100'
            }`}
          >
            <img src={img} alt="" className="w-full h-full object-cover" loading="lazy" />
          </button>
        ))}
      </div>

      {/* Color swatches */}
      {colors.length > 1 && (
        <div className="flex flex-wrap gap-2">
          {colors.map((color) => (
            <button
              key={color}
              onClick={() => {
                onColorSelect(selectedColor === color ? null : color);
                setActiveIndex(0);
              }}
              className={`px-4 py-2 rounded-full text-sm font-medium border transition ${
                selectedColor === color
                  ? 'bg-black text-white border-black dark:bg-white dark:text-black dark:border-white'
                  : 'bg-white text-gray-700 border-gray-300 hover:border-gray-500 dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600'
              }`}
            >
              {color}
            </button>
          ))}
        </div>
      )}
    </div>
  );
}
