"use client";

import { cn } from "@/lib/utils";
import { forwardRef, useCallback, useImperativeHandle, useState, useEffect } from "react";

export interface Trash2IconHandle {
 startAnimation: () => void;
 stopAnimation: () => void;
}

interface Trash2IconProps extends React.HTMLProps<HTMLDivElement> {
 size?: number;
 duration?: number;
 isAnimated?: boolean;
}

const Trash2Icon = forwardRef<Trash2IconHandle, Trash2IconProps>(
 (
  {
   onMouseEnter: onMouseEnterProp,
   onMouseLeave: onMouseLeaveProp,
   className,
   size = 24,
   isAnimated = true,
   ...props
  },
  ref,
 ) => {
  const [isAnimating, setIsAnimating] = useState(false);

  // Trigger animation when isAnimated prop changes to true
  useEffect(() => {
   if (isAnimated) {
    setIsAnimating(true);
    const timer = setTimeout(() => setIsAnimating(false), 800);
    return () => clearTimeout(timer);
   }
  }, [isAnimated]);

  useImperativeHandle(ref, () => {
   return {
    startAnimation: () => {
     setIsAnimating(true);
     setTimeout(() => setIsAnimating(false), 800);
    },
    stopAnimation: () => {
     setIsAnimating(false);
    },
   };
  });

  const handleEnter = useCallback(
   (e?: React.MouseEvent<HTMLDivElement>) => {
    setIsAnimating(true);
    setTimeout(() => setIsAnimating(false), 800);
    onMouseEnterProp?.(e as any);
   },
   [onMouseEnterProp],
  );

  const handleLeave = useCallback(
   (e?: React.MouseEvent<HTMLDivElement>) => {
    setIsAnimating(false);
    onMouseLeaveProp?.(e as any);
   },
   [onMouseLeaveProp],
  );

  return (
   <div
    className={cn(
     "inline-flex items-center justify-center",
     isAnimating && "animate-bounce",
     className
    )}
    onMouseEnter={handleEnter}
    onMouseLeave={handleLeave}
    {...props}
   >
    <svg
     xmlns="http://www.w3.org/2000/svg"
     width={size}
     height={size}
     viewBox="0 0 24 24"
     fill="none"
     stroke="currentColor"
     strokeWidth="2"
     strokeLinecap="round"
     strokeLinejoin="round"
     className={isAnimating ? "animate-pulse" : ""}
    >
     <path d="M10 11v6" />
     <path d="M14 11v6" />
     <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6" />
     <path d="M3 6h18" stroke="currentColor" />
     <path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
    </svg>
   </div>
  );
 },
);

Trash2Icon.displayName = "Trash2Icon";
export { Trash2Icon };
