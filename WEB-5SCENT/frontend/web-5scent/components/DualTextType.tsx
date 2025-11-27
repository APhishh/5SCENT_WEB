'use client';

import { useEffect, useState, useRef, CSSProperties } from 'react';

interface DualTextTypeProps {
  headerText: string;
  bodyText: string;
  headerClassName?: string;
  bodyClassName?: string;
  bodyStyle?: CSSProperties;
  typingSpeed?: number;
  deletingSpeed?: number;
  pauseBetweenPhases?: number;
}

type Phase = 'typing-header' | 'typing-body' | 'pausing-after-typing' | 'deleting-body' | 'deleting-header' | 'pausing-after-deleting';

const DualTextType = ({
  headerText,
  bodyText,
  headerClassName = '',
  bodyClassName = '',
  bodyStyle = {},
  typingSpeed = 80,
  deletingSpeed = 60,
  pauseBetweenPhases = 500,
}: DualTextTypeProps) => {
  const [headerVisibleText, setHeaderVisibleText] = useState('');
  const [bodyVisibleText, setBodyVisibleText] = useState('');
  const [phase, setPhase] = useState<Phase>('typing-header');
  const [charIndex, setCharIndex] = useState(0);
  const timeoutRef = useRef<NodeJS.Timeout | null>(null);
  const referenceRef = useRef<HTMLDivElement>(null);
  const [referenceHeight, setReferenceHeight] = useState(0);

  // On mount, measure the height of full text using invisible reference
  useEffect(() => {
    if (referenceRef.current) {
      const height = referenceRef.current.getBoundingClientRect().height;
      setReferenceHeight(height);
    }
  }, [headerText, bodyText, headerClassName, bodyClassName]);

  useEffect(() => {
    const executePhase = () => {
      switch (phase) {
        case 'typing-header': {
          if (charIndex < headerText.length) {
            timeoutRef.current = setTimeout(() => {
              setHeaderVisibleText(headerText.slice(0, charIndex + 1));
              setCharIndex(charIndex + 1);
            }, typingSpeed);
          } else {
            // Header typing complete, move to body
            setPhase('typing-body');
            setCharIndex(0);
          }
          break;
        }

        case 'typing-body': {
          if (charIndex < bodyText.length) {
            timeoutRef.current = setTimeout(() => {
              setBodyVisibleText(bodyText.slice(0, charIndex + 1));
              setCharIndex(charIndex + 1);
            }, typingSpeed);
          } else {
            // Body typing complete, pause then start deleting
            setPhase('pausing-after-typing');
            setCharIndex(0);
          }
          break;
        }

        case 'pausing-after-typing': {
          timeoutRef.current = setTimeout(() => {
            setPhase('deleting-body');
            setCharIndex(bodyText.length);
          }, pauseBetweenPhases);
          break;
        }

        case 'deleting-body': {
          if (charIndex > 0) {
            timeoutRef.current = setTimeout(() => {
              setBodyVisibleText(bodyText.slice(0, charIndex - 1));
              setCharIndex(charIndex - 1);
            }, deletingSpeed);
          } else {
            // Body deleted, now delete header
            setPhase('deleting-header');
            setCharIndex(headerText.length);
          }
          break;
        }

        case 'deleting-header': {
          if (charIndex > 0) {
            timeoutRef.current = setTimeout(() => {
              setHeaderVisibleText(headerText.slice(0, charIndex - 1));
              setCharIndex(charIndex - 1);
            }, deletingSpeed);
          } else {
            // Header deleted, pause then start again
            setPhase('pausing-after-deleting');
            setCharIndex(0);
          }
          break;
        }

        case 'pausing-after-deleting': {
          timeoutRef.current = setTimeout(() => {
            setPhase('typing-header');
            setCharIndex(0);
          }, pauseBetweenPhases);
          break;
        }
      }
    };

    executePhase();

    return () => {
      if (timeoutRef.current) {
        clearTimeout(timeoutRef.current);
      }
    };
  }, [phase, charIndex, headerText, bodyText, typingSpeed, deletingSpeed, pauseBetweenPhases]);

  return (
    <div style={{ position: 'relative', minHeight: `${referenceHeight}px` }}>
      {/* Invisible reference copy for measuring full text height - positioned absolutely so it doesn't affect layout */}
      <div
        ref={referenceRef}
        style={{
          position: 'absolute',
          visibility: 'hidden',
          pointerEvents: 'none',
          top: 0,
          left: 0,
        }}
      >
        <h2 className={headerClassName}>
          {headerText}
        </h2>
        <p className={bodyClassName} style={bodyStyle}>
          {bodyText}
        </p>
      </div>

      {/* Visible animated text - always displayed */}
      <h2 className={headerClassName}>
        {headerVisibleText}
      </h2>
      <p className={bodyClassName} style={bodyStyle}>
        {bodyVisibleText}
      </p>
    </div>
  );
};

export default DualTextType;
